<?php

echo view(app('sage.view'), app('sage.data'))->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sage document; Blade templates escape at the echo site
