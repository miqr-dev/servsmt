<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showRoles?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showRoles: false,
});

const { getInitials } = useInitials();

const fullName = computed(() => [props.user.vorname, props.user.name].filter(Boolean).join(' '));

const displayRoles = computed<string[]>(() => props.user.assignedRoles ?? props.user.roles ?? []);

const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="fullName" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(fullName) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ fullName }}</span>
        <!-- one role per row; the roles really assigned (a Super_Admin's
             effective `roles` list contains every role, which isn't useful here) -->
        <ul v-if="showRoles && displayRoles.length" class="text-muted-foreground mt-1 flex flex-col gap-0.5 text-xs">
            <li v-for="role in displayRoles" :key="role" class="flex items-center gap-1.5">
                <span class="bg-primary/60 size-1.5 shrink-0 rounded-full" />
                <span class="truncate">{{ role.replace(/_/g, ' ') }}</span>
            </li>
        </ul>
    </div>
</template>
