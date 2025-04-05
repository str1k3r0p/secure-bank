# Banking DVWA Project

A cyberpunk-themed banking website that intentionally implements common web security vulnerabilities to demonstrate security concepts.

## Overview

This project creates a functional banking website with deliberately vulnerable implementations of common security issues. It follows the concept of DVWA (Damn Vulnerable Web Application) but presents vulnerabilities in the context of a real-world banking application with a cyberpunk aesthetic.

## Key Features

- **Banking Functionality**: User registration, login, account management, transfers, statements
- **Bank Manager Portal**: Separate administrative interface
- **Cyberpunk Animated Theme**: Visually engaging cyberpunk-inspired UI
- **Security Vulnerability Demonstrations**:
  - Multiple vulnerabilities running in parallel
  - Three security levels per vulnerability (low, medium, high)
  - Real-time code comparison between security implementations
  - Interactive exploitation environment

## Implemented Vulnerabilities

The application demonstrates the following vulnerabilities:

1. **Brute Force Attacks**: Authentication vulnerabilities
2. **Command Injection**: OS command execution vulnerabilities
3. **SQL Injection**: Database query vulnerabilities
4. **Directory Traversal**: File system access vulnerabilities
5. **Cross-Site Scripting (XSS)**: Client-side code injection vulnerabilities

## Installation Requirements

- XAMPP (or equivalent with PHP 7.4+, MySQL 5.7+, Apache)
- Apache mod_rewrite enabled
- GD Library
- Writable permissions for config and logs directories

## Installation Instructions

1. Place the entire `bank_dvwa_project` directory in your XAMPP's `htdocs` folder
2. Start Apache and MySQL services in XAMPP
3. Navigate to `http://localhost/bank_dvwa_project/setup.php` in your browser
4. Follow the installation wizard instructions

## Security Notice

This application contains intentional security vulnerabilities for educational purposes. **DO NOT** deploy this application on a production server or expose it to the public internet. It is designed to be run locally in a controlled environment for learning purposes only.

## Documentation

Detailed documentation is available in the `docs` directory:

- Installation and configuration: `docs/installation.md`
- XAMPP-specific setup: `docs/xampp_setup.md`
- Usage guide: `docs/usage.md`
- Vulnerability details: `docs/vulnerabilities/`

## Thesis/Book

This project is accompanied by a comprehensive thesis/book explaining web application security concepts, implementation details, and mitigation strategies. The book material is available in the `docs/thesis` directory.

## License

This project is for educational purposes only. See the LICENSE file for details.