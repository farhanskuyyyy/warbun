<?php

return ['gateway' => env('PAYMENT_GATEWAY', 'manual'), 'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET')];
