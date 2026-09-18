<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';

type Props = {
    user: User;
};

defineProps<Props>();

// No "log out" item here on purpose: this app authenticates via Windows/LDAP SSO
// (see WindowsAuthenticate middleware) and logout was intentionally disabled for
// that flow in the old admin_header.blade.php - carrying that behavior forward.
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-roles="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" href="/profile">
                <Settings class="mr-2 h-4 w-4" />
                Profil
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
