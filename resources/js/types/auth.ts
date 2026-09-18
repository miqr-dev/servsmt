export type User = {
    id: number;
    name: string;
    vorname?: string;
    email: string;
    username?: string;
    position?: string;
    avatar?: string;
    roles?: string[];
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
