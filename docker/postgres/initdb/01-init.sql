CREATE TABLE demo (
    id serial PRIMARY KEY,
    created_at timestamptz NOT NULL DEFAULT now(),
    note text
);
