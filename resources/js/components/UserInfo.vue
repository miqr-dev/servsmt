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

const fullName = computed(() =>
    [props.user.vorname, props.user.name].filter(Boolean).join(' '),
);

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== '',
);
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
        <span
            v-if="showRoles && user.roles?.length"
            class="text-muted-foreground truncate text-xs"
            >{{ user.roles.join(', ') }}</span
        >
    </div>
</template>
