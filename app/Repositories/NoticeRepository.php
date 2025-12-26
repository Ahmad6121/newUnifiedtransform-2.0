<?php

namespace App\Repositories;

use App\Models\Notice;
use App\Models\User;

class NoticeRepository
{
    public function store(array $data)
    {
        $type = $data['audience_type'] ?? 'all';

        $payload = [
            'notice'         => $data['notice'] ?? '',
            'session_id'     => $data['session_id'] ?? null,
            'audience_type'  => $type,
            'audience_roles' => null,
            'audience_users' => null,
        ];

        if ($type === 'roles') {
            $roles = $data['audience_roles'] ?? [];
            if (!is_array($roles)) $roles = [];

            $roles = array_map(function ($r) {
                $r = strtolower(trim((string)$r));
                return str_replace(' ', '_', $r);
            }, $roles);

            $payload['audience_roles'] = array_values(array_unique(array_filter($roles)));
        }

        if ($type === 'users') {
            $raw = (string)($data['audience_users'] ?? '');
            $ids = preg_split('/\s*,\s*/', trim($raw));
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            $payload['audience_users'] = $ids;
        }

        return Notice::create($payload);
    }

    public function getAll($session_id)
    {
        return Notice::where('session_id', $session_id)
            ->orderBy('id', 'desc')
            ->simplePaginate(3);
    }

    public function getAllVisible($session_id, User $user)
    {
        return Notice::visibleTo($user)
            ->where('session_id', $session_id)
            ->orderBy('id', 'desc')
            ->simplePaginate(3);
    }
}
