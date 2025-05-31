
# Админ-панель для расширения "Text Tabs"

Современная веб-админ-панель для управления пользователями и данными браузерного расширения "Text Tabs". Построена на React + TypeScript с PHP бэкендом.

## Архитектура проекта

- **Frontend**: React 18 + TypeScript + Vite + Tailwind CSS + shadcn/ui
- **Backend**: PHP 8+ + MySQL/MariaDB
- **Развертывание**: Один проект на виртуальном PHP хостинге

## Структура проекта

```
project/
├── src/                     # React приложение
│   ├── components/          # React компоненты
│   ├── pages/              # Страницы приложения
│   ├── hooks/              # React хуки
│   ├── services/           # Сервисы для API
│   └── config/             # Конфигурация
├── api/                    # PHP бэкенд
│   ├── config/             # Конфигурационные файлы PHP
│   ├── includes/           # Общие PHP модули
│   ├── users.php           # API для пользователей
│   ├── pages.php           # API для страниц
│   ├── dashboard.php       # API для дашборда
│   └── settings.php        # API для настроек
├── dist/                   # Собранное React приложение (после npm run build)
└── package.json            # Зависимости Node.js
```

## Установка и настройка

### 1. Требования к серверу

- **Web-сервер**: Apache 2.4+ или Nginx
- **PHP**: версия 8.0 или выше
- **База данных**: MySQL 8.0+ или MariaDB 10.3+
- **Расширения PHP**: 
  - `pdo`
  - `pdo_mysql`
  - `json`
  - `mbstring`
  - `openssl`

### 2. Развертывание на виртуальном хостинге

#### Шаг 1: Подготовка локально

1. **Клонируйте проект**:
```bash
git clone <repository-url>
cd text-tabs-admin
```

2. **Установите зависимости**:
```bash
npm install
```

3. **Соберите React приложение**:
```bash
npm run build
```

#### Шаг 2: Загрузка на хостинг

1. **Загрузите файлы на хостинг**:
   - Содержимое папки `dist/` → в корень сайта (`public_html/` или аналогичная папка)
   - Папку `api/` → в `public_html/api/`

2. **Настройте права доступа**:
```bash
chmod 644 api/config/*.php
chmod 755 api/
chmod 644 api/*.php
```

#### Шаг 3: Настройка веб-сервера

**Для Apache** (файл `.htaccess` в корне):
```apache
RewriteEngine On
RewriteBase /

# Обработка React Router
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/api/
RewriteRule . /index.html [L]

# Настройки безопасности для API
<Files ~ "\.php$">
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</Files>
```

### 3. Настройка базы данных

#### Создание базы данных

1. **Создайте базу данных MySQL**:
```sql
CREATE DATABASE texttabs_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Откройте админ-панель**: `https://yourdomain.com`

3. **Перейдите в "Настройки"** и заполните данные подключения к БД:
   - **Хост**: обычно `localhost`
   - **Порт**: обычно `3306`
   - **Имя базы данных**: `texttabs_admin` (или ваше имя)
   - **Пользователь**: имя пользователя БД
   - **Пароль**: пароль от БД

4. **Нажмите "Тестировать подключение"** для проверки

5. **Нажмите "Создать таблицы"** для автоматического создания всех необходимых таблиц

6. **Нажмите "Сохранить настройки"** для сохранения конфигурации

#### Структура таблиц

Система автоматически создает следующие таблицы:

**1. users** - Пользователи расширения:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email_verification_token VARCHAR(100) DEFAULT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password_reset_token VARCHAR(100) DEFAULT NULL,
    password_reset_expires_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('pending', 'verified', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
);
```

**2. user_pages** - Страницы пользователей:
```sql
CREATE TABLE user_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

**3. admin_users** - Администраторы панели:
```sql
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username)
);
```

**4. system_settings** - Настройки системы:
```sql
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);
```

При создании таблиц автоматически создается первый администратор:
- **Логин**: `admin`
- **Пароль**: `admin123`
- **Обязательно смените пароль после первого входа!**

## API эндпоинты

### Пользователи (/api/users.php)
- `GET ?action=get_users` - список пользователей с фильтрацией
- `GET ?action=get_user&id=123` - получение пользователя по ID
- `POST action=create_user` - создание пользователя
- `PUT action=update_user` - обновление пользователя
- `DELETE ?action=delete_user&id=123` - удаление пользователя

### Страницы (/api/pages.php)
- `GET ?action=get_pages` - список страниц с фильтрацией
- `GET ?action=get_page&id=123` - получение страницы по ID
- `POST action=create_page` - создание страницы
- `PUT action=update_page` - обновление страницы
- `DELETE ?action=delete_page&id=123` - удаление страницы

### Dashboard (/api/dashboard.php)
- `GET ?action=get_dashboard_data` - статистика для дашборда

### Настройки (/api/settings.php)
- `POST action=test_db_connection` - тестирование подключения к БД
- `POST action=create_tables` - создание таблиц в БД
- `POST action=save_db_config` - сохранение конфигурации БД

## Функциональность

### Dashboard
- Общая статистика (пользователи, страницы, активность)
- Графики роста и активности
- Список последних регистраций

### Управление пользователями
- Просмотр списка пользователей с фильтрацией
- Создание новых пользователей
- Поиск по email
- Фильтрация по статусу
- Пагинация

### Управление страницами
- Просмотр всех текстовых страниц
- Создание страниц для пользователей
- Поиск по содержимому
- Фильтрация по пользователям

### Настройки
- Конфигурация подключения к БД
- Тестирование подключения
- Автоматическое создание таблиц
- Сохранение настроек в файл

## Безопасность

### Обязательно к выполнению:

1. **Смените пароль администратора** сразу после установки
2. **Используйте HTTPS** в production
3. **Настройте файрвол** для защиты БД
4. **Ограничьте доступ** к папке `/api/config/`
5. **Регулярно создавайте резервные копии** БД

### Права доступа к файлам:
```bash
# PHP файлы
chmod 644 api/*.php
chmod 644 api/config/*.php

# Директории
chmod 755 api/
chmod 755 api/config/

# Конфиги (более строгие права)
chmod 600 api/config/database.php
```

## Разработка

### Локальная разработка

1. **Установите зависимости**:
```bash
npm install
```

2. **Запустите dev-сервер**:
```bash
npm run dev
```

3. **Настройте локальную БД** через интерфейс настроек

### Сборка для production

```bash
npm run build
```

Собранные файлы появятся в папке `dist/`.

## Устранение проблем

### Проблемы с подключением к БД:
1. Проверьте данные в настройках БД
2. Убедитесь что пользователь БД имеет нужные права
3. Проверьте что MySQL сервер запущен

### Проблемы с API:
1. Проверьте права доступа к файлам PHP
2. Убедитесь что mod_rewrite включен (Apache)
3. Проверьте логи веб-сервера

### Проблемы с роутингом React:
1. Проверьте настройки `.htaccess` или Nginx
2. Убедитесь что `index.html` доступен

## Поддержка

Для получения помощи:
1. Проверьте логи веб-сервера
2. Включите отображение ошибок PHP в development
3. Используйте инструменты разработчика браузера для отладки

## Лицензия

Этот проект распространяется под MIT лицензией.
