-- migrations/init.sql
-- Schema database minimale (compatibile PostgreSQL e MySQL)

-- ================================================
-- Tabella Users
-- ================================================
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Tabella Posts
-- ================================================
CREATE TABLE IF NOT EXISTS posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Dati di esempio
-- ================================================
INSERT INTO users (username, email) VALUES
    ('alice', 'alice@example.com'),
    ('bob', 'bob@example.com'),
    ('charlie', 'charlie@example.com')
ON CONFLICT DO NOTHING;  -- PostgreSQL syntax, MySQL ignora

INSERT INTO posts (user_id, title, content) VALUES
    (1, 'Primo post', 'Contenuto del primo post'),
    (2, 'Secondo post', 'Contenuto del secondo post')
ON CONFLICT DO NOTHING;
