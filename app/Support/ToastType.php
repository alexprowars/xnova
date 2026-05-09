<?php

namespace App\Support;

enum ToastType: string
{
	case SUCCESS = 'success';
	case WARNING = 'warning';
	case ERROR = 'error';
	case INFO = 'info';
	case DEFAULT = 'default';
}
