<?php
Auth::logout();
header('Location: ' . BASE_URL);
exit;
