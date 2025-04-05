-- Banking DVWA Project
-- Sample Data

-- Admin user (password: admin123)
INSERT INTO `bdv_users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `status`, `last_login`, `created_at`)
VALUES (
  'admin', 
  '$2y$12$R9VlqUYKscHc4TpqOJtvM.bw/CIoRU3tQw7d.7YaeRkMzK1KaSYWO', 
  'admin@example.com', 
  'Admin', 
  'User', 
  'admin', 
  'active', 
  NOW(), 
  NOW()
);

-- Regular users (password: password123)
INSERT INTO `bdv_users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `status`, `last_login`, `created_at`)
VALUES 
(
  'johndoe', 
  '$2y$12$P.c4C38AXGd66QuMl/09suaXzxITZOl3N8uGU/D5tLDVKZWjI6VjK', 
  'john.doe@example.com', 
  'John', 
  'Doe', 
  'user', 
  'active', 
  NOW() - INTERVAL 2 DAY, 
  NOW() - INTERVAL 30 DAY
),
(
  'janedoe', 
  '$2y$12$3vJxl7JGxJLEMG0A./6bDOBD8/Xf6bhP7vYq8CcJN.jsXP2HwdT/O', 
  'jane.doe@example.com', 
  'Jane', 
  'Doe', 
  'user', 
  'active', 
  NOW() - INTERVAL 1 DAY, 
  NOW() - INTERVAL 25 DAY
),
(
  'samsmith', 
  '$2y$12$WuARoOdLJK0CNxoDOzqb7.e7M3qh/nNRQq14NtCwxX5D9Hxwm3nMS', 
  'sam.smith@example.com', 
  'Sam', 
  'Smith', 
  'user', 
  'active', 
  NOW() - INTERVAL 3 DAY, 
  NOW() - INTERVAL 20 DAY
),
(
  'alexjones', 
  '$2y$12$8z.Jl9/CJNpak1Nf/ytG/.iXWOyMVxPdDJqFfZ0WdxAeEXZ5WU5i2', 
  'alex.jones@example.com', 
  'Alex', 
  'Jones', 
  'user', 
  'active', 
  NOW() - INTERVAL 5 DAY, 
  NOW() - INTERVAL 15 DAY
);

-- Demo manager (password: manager123)
INSERT INTO `bdv_users` (`username`, `password`, `email`, `first_name`, `last_name`, `role`, `status`, `last_login`, `created_at`)
VALUES (
  'manager', 
  '$2y$12$R8RXLTVBs5XUBdH1L/N5qeVdYJ0F3C9AmO7uIC4NVqXlTc4QgZSI.', 
  'manager@example.com', 
  'Bank', 
  'Manager', 
  'manager', 
  'active', 
  NOW(), 
  NOW()
);

-- Accounts for users
INSERT INTO `bdv_accounts` (`user_id`, `account_number`, `account_type`, `balance`, `currency`, `status`, `created_at`)
VALUES 
-- John Doe's accounts
(2, '20250301123456', 'checking', 5240.75, 'USD', 'active', NOW() - INTERVAL 30 DAY),
(2, '20250301123457', 'savings', 12500.00, 'USD', 'active', NOW() - INTERVAL 28 DAY),

-- Jane Doe's accounts
(3, '20250301123458', 'checking', 3210.45, 'USD', 'active', NOW() - INTERVAL 25 DAY),
(3, '20250301123459', 'business', 75000.00, 'USD', 'active', NOW() - INTERVAL 20 DAY),

-- Sam Smith's accounts
(4, '20250301123460', 'checking', 1750.25, 'USD', 'active', NOW() - INTERVAL 20 DAY),
(4, '20250301123461', 'savings', 8900.00, 'USD', 'active', NOW() - INTERVAL 18 DAY),

-- Alex Jones's accounts
(5, '20250301123462', 'checking', 4200.00, 'USD', 'active', NOW() - INTERVAL 15 DAY);

-- Transactions for John Doe
INSERT INTO `bdv_transactions` (`account_id`, `transaction_type`, `amount`, `description`, `reference`, `to_account_id`, `status`, `created_at`)
VALUES 
(1, 'deposit', 1000.00, 'Initial deposit', 'TX20250301001', NULL, 'completed', NOW() - INTERVAL 30 DAY),
(1, 'deposit', 2500.00, 'Salary payment', 'TX20250301002', NULL, 'completed', NOW() - INTERVAL 25 DAY),
(1, 'withdrawal', -200.00, 'ATM withdrawal', 'TX20250301003', NULL, 'completed', NOW() - INTERVAL 20 DAY),
(1, 'transfer', -500.00, 'Transfer to savings', 'TX20250301004', 2, 'completed', NOW() - INTERVAL 15 DAY),
(2, 'transfer', 500.00, 'Transfer from checking', 'TX20250301005', 1, 'completed', NOW() - INTERVAL 15 DAY),
(1, 'payment', -59.25, 'Electric bill payment', 'TX20250301006', NULL, 'completed', NOW() - INTERVAL 10 DAY),
(2, 'deposit', 12000.00, 'Annual bonus', 'TX20250301007', NULL, 'completed', NOW() - INTERVAL 5 DAY);

-- Transactions for Jane Doe
INSERT INTO `bdv_transactions` (`account_id`, `transaction_type`, `amount`, `description`, `reference`, `to_account_id`, `status`, `created_at`)
VALUES 
(3, 'deposit', 3000.00, 'Initial deposit', 'TX20250301008', NULL, 'completed', NOW() - INTERVAL 25 DAY),
(3, 'withdrawal', -300.00, 'Shopping', 'TX20250301009', NULL, 'completed', NOW() - INTERVAL 20 DAY),
(4, 'deposit', 75000.00, 'Business investment', 'TX20250301010', NULL, 'completed', NOW() - INTERVAL 20 DAY),
(3, 'payment', -89.55, 'Internet bill payment', 'TX20250301011', NULL, 'completed', NOW() - INTERVAL 15 DAY),
(3, 'transfer', -400.00, 'Transfer to Alex', 'TX20250301012', 7, 'completed', NOW() - INTERVAL 10 DAY),
(7, 'transfer', 400.00, 'Transfer from Jane', 'TX20250301013', 3, 'completed', NOW() - INTERVAL 10 DAY);

-- Transactions for Sam Smith
INSERT INTO `bdv_transactions` (`account_id`, `transaction_type`, `amount`, `description`, `reference`, `to_account_id`, `status`, `created_at`)
VALUES 
(5, 'deposit', 2000.00, 'Initial deposit', 'TX20250301014', NULL, 'completed', NOW() - INTERVAL 20 DAY),
(5, 'withdrawal', -150.00, 'Grocery shopping', 'TX20250301015', NULL, 'completed', NOW() - INTERVAL 18 DAY),
(5, 'withdrawal', -99.75, 'Restaurant', 'TX20250301016', NULL, 'completed', NOW() - INTERVAL 15 DAY),
(6, 'deposit', 8900.00, 'Savings deposit', 'TX20250301017', NULL, 'completed', NOW() - INTERVAL 18 DAY);

-- Transactions for Alex Jones
INSERT INTO `bdv_transactions` (`account_id`, `transaction_type`, `amount`, `description`, `reference`, `to_account_id`, `status`, `created_at`)
VALUES 
(7, 'deposit', 4000.00, 'Initial deposit', 'TX20250301018', NULL, 'completed', NOW() - INTERVAL 15 DAY),
(7, 'withdrawal', -200.00, 'Cash withdrawal', 'TX20250301019', NULL, 'completed', NOW() - INTERVAL 12 DAY);

-- Security levels default settings
INSERT INTO `bdv_security_levels` (`vulnerability`, `level`, `description`, `user_id`, `updated_at`)
VALUES 
('brute_force', 'low', 'No protection against brute force attacks', 1, NOW()),
('cmd_injection', 'low', 'No input validation or sanitization for command execution', 1, NOW()),
('sql_injection', 'low', 'Direct inclusion of user input in SQL queries', 1, NOW()),
('directory_traversal', 'low', 'No path validation for file access', 1, NOW()),
('xss', 'low', 'No output encoding or input validation', 1, NOW());