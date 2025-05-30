# Руководство по внесению доработок

Спасибо за интерес к нашему проекту! Я рад, что вы хотите помочь сделать его лучше. Это руководство поможет вам понять, как можно внести свой вклад.

## Как начать?

1. **Fork репозитория**: Создайте свою копию репозитория, нажав кнопку "Fork" в правом верхнем углу страницы.
2. **Клонируйте репозиторий**:
   ```
   git clone https://github.com/your-username/your-repo.git
   cd your-repo
   ```
3. **Настройте окружение**:
    - Убедитесь, что у вас установлены необходимые зависимости (например, PHP 8.1+ и Composer).
    - Выполните команду для установки зависимостей:
      ```
      composer install
      ```

## Как внести изменения?

1. Создайте новую ветку для ваших изменений:
   ```
   git checkout -b feature/your-feature-name
   ```
2. Внесите изменения в код.
3. Проверьте, что ваш код соответствует стандартам проекта (см. раздел "Правила оформления кода").
4. Запустите тесты, если они есть:
   ```
   ./vendor/bin/phpunit
   ```

## Как отправить pull request?

1. Зафиксируйте свои изменения:
   ```
   git add .
   git commit -m "Описание ваших изменений"
   ```
2. Отправьте изменения в ваш fork:
   ```
   git push origin feature/your-feature-name
   ```
3. Перейдите в оригинальный репозиторий и создайте pull request из вашей ветки.
4. Убедитесь, что ваш pull request содержит:
    - Четкое описание изменений.
    - Информацию о том, как эти изменения влияют на проект.

## Правила оформления кода

Проект придерживается следующих стандартов:
- Используйте [PSR-12](https://www.php-fig.org/psr/psr-12/) для форматирования кода.
- Добавляйте комментарии к сложным частям кода.
- Пишите тесты для нового функционала.

## Процесс ревью

1. После создания pull request я проверю ваши изменения.
2. Если потребуются правки, оставлю комментарии. Пожалуйста, будьте готовы обсудить их.
3. После одобрения изменения будут включены в основную ветку.

## Контакты

Если у вас возникли вопросы или вы не уверены, как внести изменения:
- Создайте issue с меткой `question`.

Спасибо за ваш вклад!