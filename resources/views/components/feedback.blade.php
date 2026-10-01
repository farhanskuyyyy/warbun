@if(session('success'))<p role="status" class="mb-4 rounded border border-green-300 bg-green-50 p-3 text-green-900">{{ session('success') }}</p>@endif
@if(session('error'))<p role="alert" class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-900">{{ session('error') }}</p>@endif
@if($errors->any())<div role="alert" class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-red-900"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
