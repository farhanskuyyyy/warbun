import json
import os
from pathlib import Path
import select
import signal
import subprocess
import time


PROJECT = Path(__file__).resolve().parents[1]


def configured_servers(directory):
    result = subprocess.run(
        ['codex', 'mcp', 'list', '--json'], cwd=directory,
        capture_output=True, text=True, check=True,
    )
    return {item['name'] for item in json.loads(result.stdout) if item['enabled']}


def verify_server(name):
    configuration = json.loads(subprocess.run(
        ['codex', 'mcp', 'get', name, '--json'], cwd=PROJECT,
        capture_output=True, text=True, check=True,
    ).stdout)['transport']
    process = subprocess.Popen(
        [configuration['command'], *configuration['args']],
        cwd=PROJECT / 'resources' if name == 'laravel-boost' else PROJECT,
        stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.DEVNULL,
        bufsize=0, start_new_session=True,
    )
    buffered = b''

    def send(method, parameters=None, identifier=None):
        message = {'jsonrpc': '2.0', 'method': method}
        if parameters is not None:
            message['params'] = parameters
        if identifier is not None:
            message['id'] = identifier
        process.stdin.write((json.dumps(message) + '\n').encode())
        process.stdin.flush()

    def receive(identifier):
        nonlocal buffered
        deadline = time.monotonic() + 40
        while time.monotonic() < deadline:
            while b'\n' in buffered:
                line, buffered = buffered.split(b'\n', 1)
                message = json.loads(line)
                if message.get('id') == identifier:
                    if 'error' in message:
                        raise RuntimeError(name + ': JSON-RPC error')
                    return message['result']
            ready, _, _ = select.select([process.stdout], [], [], max(0, deadline - time.monotonic()))
            if not ready:
                break
            chunk = os.read(process.stdout.fileno(), 65536)
            if not chunk:
                raise RuntimeError(name + ': server closed output')
            buffered += chunk
        raise TimeoutError(name + ': MCP timeout')

    try:
        send('initialize', {
            'protocolVersion': '2024-11-05', 'capabilities': {},
            'clientInfo': {'name': 'warbun-mcp-qa', 'version': '1.0'},
        }, 1)
        receive(1)
        send('notifications/initialized')
        send('tools/list', {}, 2)
        names = {tool['name'] for tool in receive(2)['tools']}
        if name == 'context7':
            tool, arguments = 'resolve-library-id', {'libraryName': 'laravel', 'query': 'Laravel 13 routing'}
        elif name == 'github':
            tool, arguments = 'get_me', {}
        elif name == 'laravel-boost':
            tool, arguments = 'application-info', {}
        else:
            assert {'browser_navigate', 'browser_snapshot', 'browser_click', 'browser_take_screenshot'} <= names
            print(f'{name}: PASS handshake and {len(names)} tools', flush=True)
            return
        assert tool in names
        send('tools/call', {'name': tool, 'arguments': arguments}, 3)
        response = receive(3)
        assert not response.get('isError'), name + ': read call failed'
        body = '\n'.join(item.get('text', '') for item in response.get('content', []) if item.get('type') == 'text')
        if name == 'context7':
            assert '/laravel/' in body or 'Context7-compatible library ID' in body
        elif name == 'github':
            assert 'login' in body
        else:
            assert 'laravel' in body.lower()
        print(f'{name}: PASS handshake, {len(names)} tools, {tool} read call', flush=True)
    finally:
        if process.poll() is None:
            try:
                os.killpg(process.pid, signal.SIGTERM)
            except (ProcessLookupError, PermissionError):
                process.terminate()
            try:
                process.wait(timeout=3)
            except subprocess.TimeoutExpired:
                try:
                    os.killpg(process.pid, signal.SIGKILL)
                except (ProcessLookupError, PermissionError):
                    process.kill()
                process.wait()


assert {'playwright', 'context7', 'github', 'laravel-boost'} <= configured_servers(PROJECT)
assert 'laravel-boost' not in configured_servers(PROJECT.parent.parent)
print('Project-scoped configuration: PASS', flush=True)
for server_name in ['context7', 'github', 'laravel-boost', 'playwright']:
    verify_server(server_name)
