# Contributing to OpenShop

Thank you for your interest in contributing to OpenShop! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Reporting Bugs

- Use the [GitHub Issues](https://github.com/yourusername/ecomer/issues) page
- Provide a clear and descriptive title
- Include steps to reproduce the issue
- Add screenshots if applicable
- Specify your environment (OS, browser, PHP/Node versions)

### Suggesting Features

- Open an issue with the "enhancement" label
- Provide a clear description of the feature
- Explain why it would be valuable
- Consider if it fits the project's scope

### Code Contributions

#### Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/yourusername/ecomer.git
   cd ecomer
   ```

3. Set up the development environment:
   ```bash
   # Backend
   cd backend
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve

   # Frontend (new terminal)
   cd frontend
   npm install
   npm run dev
   ```

#### Creating a Pull Request

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes following the coding standards
3. Test your changes thoroughly
4. Commit your changes:
   ```bash
   git commit -m "feat: add your feature description"
   ```

5. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

6. Open a Pull Request against the `main` branch

## 📝 Coding Standards

### PHP (Backend)

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use meaningful variable and method names
- Add PHPDoc blocks for all classes and methods
- Keep methods small and focused
- Use Laravel's built-in features and conventions

### JavaScript/React (Frontend)

- Use ES6+ syntax
- Follow [Airbnb JavaScript Style Guide](https://airbnb.io/javascript/)
- Use functional components and hooks
- Add JSDoc comments for complex functions
- Keep components small and focused
- Use meaningful prop names

### General Guidelines

- Write clean, readable, and maintainable code
- Add comments for complex logic
- Follow the existing code style
- Don't leave commented-out code in final PRs
- Update documentation as needed

## 🧪 Testing

### Backend Testing

- Write unit tests for new features
- Test edge cases and error conditions
- Ensure all tests pass before submitting PR
- Aim for good test coverage

```bash
cd backend
php artisan test
```

### Frontend Testing

- Test components manually
- Check responsive design
- Test on different browsers
- Ensure accessibility standards

## 📋 Pull Request Process

1. **Update Documentation**: Update README.md if needed
2. **Test Thoroughly**: Ensure all functionality works
3. **Check Style**: Run code linters and fix any issues
4. **Update Changelog**: Add your changes to CHANGELOG.md
5. **Submit PR**: Create a detailed PR description

### PR Description Template

```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Backend tests pass
- [ ] Frontend tested manually
- [ ] Cross-browser compatibility checked

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes (or documented)
```

## 🎯 Development Priorities

### High Priority
- Bug fixes
- Security improvements
- Performance optimizations
- Documentation improvements

### Medium Priority
- New features
- Code refactoring
- Test coverage improvements
- UI/UX enhancements

### Low Priority
- Experimental features
- Nice-to-have enhancements
- Deprecation notices

## 🏆 Recognition

Contributors will be recognized in:
- README.md contributors section
- Release notes for significant contributions
- Special contributor badges in the application

## 💬 Getting Help

- Join our [Discord server](https://discord.gg/openshop)
- Check [GitHub Discussions](https://github.com/yourusername/ecomer/discussions)
- Review existing issues and PRs
- Contact maintainers via GitHub

## 📄 License

By contributing to OpenShop, you agree that your contributions will be licensed under the same MIT License as the project.

---

Thank you for contributing to OpenShop! 🎉
