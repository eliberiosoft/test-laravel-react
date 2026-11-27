<?php

namespace App\Enums;

enum PermissionName: string
{
    case WelcomeView = 'welcome.view';
    case DashboardView = 'dashboard.view';
    case ProfileView = 'profile.view';
    case ProfileUpdate = 'profile.update';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case UsersUnlock = 'users.unlock';
    case UsersImport = 'users.import';
    case SessionsRevoke = 'sessions.revoke';
}
