<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Librarian  = 'librarian';
    case Employee   = 'employee';
    case Member     = 'member';
}
