DROP TABLE transaction_state_change_event;
DROP TABLE payment_action;
DROP TABLE bank_account;
DROP TABLE paypal_account;
DROP TABLE exchange;
DROP TABLE loan;
DROP TABLE debt;
DROP TABLE transactions;
DROP TABLE reset_password_request;
DROP TABLE user;

CREATE USER 'debes_user'@'localhost' IDENTIFIED BY 'password';
CREATE USER 'debes_user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON * . * TO 'debes_user'@'%';
FLUSH PRIVILEGES;
CREATE DATABASE debes;