# Claude Code Guidelines

## Before Committing

Always run `composer check-cs` and `composer phpstan` before committing and fix any errors. Do not commit code that fails the code style check or PHPStan analysis.

## Code Style

This project uses PHPCS with WordPress coding standards. Key rules:
- `else` and `elseif` go on their own line after the closing `}`
- Inline ternaries must be on a single line with brackets around the comparison: `( $condition ) ? $a : $b`
- Follow existing patterns in the codebase for spacing, indentation (tabs), and brace placement
