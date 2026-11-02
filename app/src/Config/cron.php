<?php

declare(strict_types=1);

use App\Library\Tasks\RemoveOldSessionsCronTask;

return [
  'sessions:gc' => new RemoveOldSessionsCronTask(),
];
