# Contributing to Self Order Laravel

Thank you for considering contributing to the Self Order Laravel project! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Reporting Issues

Before creating an issue, please:

1. **Search existing issues** to avoid duplicates
2. **Use the issue template** when available
3. **Provide detailed information** including:
   - Steps to reproduce the issue
   - Expected vs actual behavior
   - Environment details (PHP version, Laravel version, etc.)
   - Screenshots or error logs if applicable

### Suggesting Features

We welcome feature suggestions! Please:

1. **Check existing feature requests** first
2. **Describe the feature** in detail
3. **Explain the use case** and benefits
4. **Consider the scope** - keep it focused and relevant

### Pull Requests

1. **Fork the repository** and create a feature branch
2. **Follow coding standards** (see below)
3. **Write tests** for new functionality
4. **Update documentation** as needed
5. **Submit a pull request** with a clear description

## 🛠️ Development Setup

### Prerequisites

- PHP >= 8.2
- Node.js >= 18.0
- MySQL >= 8.0
- Redis >= 6.0
- Composer >= 2.0

### Local Development

1. **Clone your fork**:
   ```bash
   git clone https://github.com/your-username/self-order-laravel.git
   cd self-order-laravel
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database**:
   ```bash
   php artisan migrate --seed
   ```

5. **Start development servers**:
   ```bash
   php artisan serve
   npm run dev
   ```

## 📝 Coding Standards

### PHP/Laravel

- Follow **PSR-12** coding standards
- Use **Laravel Pint** for code formatting: `./vendor/bin/pint`
- Write **PHPDoc** comments for classes and methods
- Follow **Laravel naming conventions**
- Use **type hints** and **return types**

### JavaScript/TypeScript

- Use **TypeScript** for all new code
- Follow **ESLint** configuration: `npm run lint`
- Use **Prettier** for formatting: `npm run format`
- Write **JSDoc** comments for complex functions
- Use **Vue 3 Composition API**

### Database

- Use **descriptive migration names**
- Add **proper indexes** for performance
- Include **foreign key constraints**
- Write **rollback methods** for migrations

## 🧪 Testing

### Running Tests

```bash
# PHP tests
php artisan test

# JavaScript tests (if available)
npm run test

# Browser tests
php artisan dusk
```

### Writing Tests

- **Unit tests** for models and services
- **Feature tests** for API endpoints
- **Browser tests** for user interactions
- Aim for **high test coverage**
- Use **descriptive test names**

### Test Guidelines

```php
// Good test name
public function test_user_can_create_order_with_valid_data()

// Bad test name
public function test_create_order()
```

## 📚 Documentation

### Code Documentation

- Write clear **PHPDoc** comments
- Document **complex business logic**
- Include **usage examples** where helpful
- Keep documentation **up to date**

### API Documentation

- Update **Swagger annotations** for API changes
- Include **request/response examples**
- Document **error responses**
- Test documentation accuracy

## 🔄 Git Workflow

### Branch Naming

- `feature/description` - New features
- `bugfix/description` - Bug fixes
- `hotfix/description` - Critical fixes
- `docs/description` - Documentation updates

### Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
type(scope): description

feat(auth): add two-factor authentication
fix(orders): resolve payment calculation bug
docs(api): update endpoint documentation
test(orders): add unit tests for order service
```

### Pull Request Process

1. **Create a feature branch** from `main`
2. **Make your changes** with clear commits
3. **Update tests** and documentation
4. **Run the test suite** to ensure everything passes
5. **Submit a pull request** with:
   - Clear title and description
   - Reference to related issues
   - Screenshots for UI changes
   - Breaking changes noted

## 🎯 Areas for Contribution

### High Priority

- **Bug fixes** and security improvements
- **Performance optimizations**
- **Test coverage** improvements
- **Documentation** enhancements

### Medium Priority

- **New payment methods** integration
- **Additional export formats**
- **UI/UX improvements**
- **Mobile responsiveness** enhancements

### Low Priority

- **Code refactoring**
- **Developer experience** improvements
- **Additional language support**
- **Advanced features**

## 🚀 Release Process

### Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backward-compatible functionality
- **PATCH** version for backward-compatible bug fixes

### Release Checklist

- [ ] All tests pass
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Version bumped in relevant files
- [ ] Security review completed
- [ ] Performance impact assessed

## 💬 Communication

### Getting Help

- **GitHub Issues** for bug reports and feature requests
- **GitHub Discussions** for questions and general discussion
- **Email** for security-related issues: pras.ari69@gmail.com

### Code of Conduct

- Be **respectful** and **inclusive**
- **Help others** learn and grow
- **Focus on constructive** feedback
- **Respect different** perspectives and experiences

## 🏆 Recognition

Contributors will be recognized in:

- **README.md** contributors section
- **CHANGELOG.md** for significant contributions
- **GitHub releases** notes

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Self Order Laravel! Your efforts help make this project better for everyone. 🙏
