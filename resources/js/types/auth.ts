export type User = {
    id: number;
    name: string;
    vorname?: string;
    email: string;
    username?: string;
    position?: string;
    avatar?: string;
    roles?: string[];
    /** Roles really assigned (roles = effective: a Super_Admin gets all of them). */
    assignedRoles?: string[];
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
