
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

### 2. Создание базы данных

Создайте базу данных MySQL и выполните следующие SQL-запросы:

```sql
-- Создание базы данных
CREATE DATABASE texttabs_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Таблица пользователей расширения
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
    INDEX idx_status (status),
    INDEX idx_verification_token (email_verification_token),
    INDEX idx_reset_token (password_reset_token)
);

-- Таблица страниц пользователей
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

-- Таблица администраторов панели
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

-- Создание первого администратора (пароль: admin123)
INSERT INTO admin_users (username, password_hash, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Таблица настроек системы
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Вставка базовых настроек
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('smtp_host', '', 'SMTP сервер для отправки почты'),
('smtp_port', '587', 'Порт SMTP сервера'),
('smtp_username', '', 'Имя пользователя SMTP'),
('smtp_password', '', 'Пароль SMTP'),
('smtp_encryption', 'tls', 'Тип шифрования (tls/ssl)'),
('site_url', 'https://yourdomain.com', 'URL сайта'),
('admin_email', 'admin@yourdomain.com', 'Email администратора');
```

### 3. Настройка на виртуальном хостинге

#### Шаг 1: Загрузка файлов

1. **Соберите React приложение локально**:
```bash
npm install
npm run build
```

2. **Загрузите файлы на хостинг**:
   - Содержимое папки `dist/` → в корень сайта (`public_html/` или аналогичная папка)
   - Папку `api/` → в `public_html/api/`

#### Шаг 2: Настройка прав доступа

Установите права доступа на файлы:
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

**Для Nginx**:
```nginx
location / {
    try_files $uri $uri/ /index.html;
}

location /api/ {
    try_files $uri $uri/ =404;
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 4. Настройка базы данных через админ-панель

1. Откройте админ-панель в браузере: `https://yourdomain.com`
2. Войдите под администратором (логин: `admin`, пароль: `admin123`)
3. Перейдите в **Настройки**
4. В разделе **"Настройки базы данных"** введите данные:
   - **Хост**: обычно `localhost`
   - **Порт**: обычно `3306`
   - **Имя базы данных**: `texttabs_admin` (или ваше имя)
   - **Пользователь**: имя пользователя БД
   - **Пароль**: пароль от БД
5. Нажмите **"Тестировать подключение"**
6. Если тест прошел успешно, нажмите **"Сохранить настройки"**

### 5. Настройка конфигурационных файлов

#### api/config/config.php
Отредактируйте основные настройки:

```php
// Измените секретные ключи для production
define('JWT_SECRET_KEY', 'ваш-уникальный-jwt-ключ-минимум-32-символа');
define('PASSWORD_SALT', 'ваш-уникальный-соль-для-паролей');

// Настройки окружения
$environment = 'production'; // или 'development'
```

### 6. Проверка работы

1. **Откройте админ-панель**: `https://yourdomain.com`
2. **Проверьте авторизацию**: логин `admin`, пароль `admin123`
3. **Проверьте Dashboard**: должна отображаться статистика
4. **Проверьте API**: откройте `https://yourdomain.com/api/dashboard.php?action=get_dashboard_data`

## Файлы для редактирования

### Обязательно к изменению:

1. **api/config/config.php**:
   - `JWT_SECRET_KEY` - замените на уникальный ключ
   - `PASSWORD_SALT` - замените на уникальную соль
   - Настройки CORS при необходимости

2. **База данных**:
   - Настройте через админ-панель или отредактируйте `api/config/database.php`

3. **Данные администратора**:
   - Смените пароль первого администратора через SQL или создайте нового

### Опционально:

1. **src/config/api.ts**:
   - `BASE_URL` если API размещено не в `/api/`

2. **vite.config.ts**:
   - `base` если приложение размещено в поддиректории

## Структура API эндпоинтов

### Аутентификация
- `POST /api/auth.php` - вход администратора
- `GET /api/auth.php?action=verify` - проверка токена

### Dashboard
- `GET /api/dashboard.php?action=get_dashboard_data` - статистика

### Пользователи
- `GET /api/users.php?action=get_users` - список пользователей
- `POST /api/users.php` - создание пользователя
- `PUT /api/users.php` - редактирование пользователя
- `DELETE /api/users.php` - удаление пользователя

### Настройки
- `POST /api/settings.php` - управление настройками
- `POST /api/settings.php` (action: test_db_connection) - тест БД
- `POST /api/settings.php` (action: save_db_config) - сохранение конфига БД

## Безопасность

### Важные моменты:

1. **Смените все секретные ключи** в `api/config/config.php`
2. **Смените пароль администратора** по умолчанию
3. **Используйте HTTPS** в production
4. **Настройте файрвол** для защиты БД
5. **Регулярно обновляйте** PHP и зависимости

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
