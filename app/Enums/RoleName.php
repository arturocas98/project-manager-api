<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'Admin';
    case ProjectManager = 'Project Manager';
    case Collaborator = 'Collaborator';
}
