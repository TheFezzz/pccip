<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require __DIR__ . '/mailer.php';
require_admin();

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Гарантируем наличие таблицы с предложенными историями.
 */
function ensure_story_suggestions_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `story_suggestions` (
            `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `suggested_by_name` VARCHAR(255) NOT NULL,
            `suggested_by_email` VARCHAR(255) DEFAULT NULL,
            `title`             VARCHAR(255) NOT NULL,
            `content`           TEXT         NOT NULL,
            `image`             VARCHAR(500) NOT NULL DEFAULT '',
            `user_id`           INT UNSIGNED DEFAULT NULL,
            `status`            ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
            `reject_reason`     TEXT         DEFAULT NULL,
            `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_status` (`status`),
            CONSTRAINT `fk_story_suggestions_user`
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Добавляем столбец reject_reason если таблица уже существовала без него
    try {
        $pdo->exec("ALTER TABLE `story_suggestions` ADD COLUMN `reject_reason` TEXT DEFAULT NULL AFTER `status`");
    } catch (PDOException $ex) {
        // столбец уже существует — игнорируем
    }
}

ensure_story_suggestions_table($pdo);

function ensure_event_suggestions_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `event_suggestions` (
            `id`                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `suggested_by_name`  VARCHAR(255)  NOT NULL,
            `suggested_by_email` VARCHAR(255)  DEFAULT NULL,
            `title`              VARCHAR(255)  NOT NULL,
            `description`        TEXT          DEFAULT NULL,
            `event_date`         VARCHAR(30)   DEFAULT NULL,
            `location`           VARCHAR(255)  DEFAULT NULL,
            `lat`                DECIMAL(10,7) DEFAULT NULL,
            `lng`                DECIMAL(10,7) DEFAULT NULL,
            `type`               ENUM('massacre','camp','village','other') NOT NULL DEFAULT 'other',
            `user_id`            INT UNSIGNED  DEFAULT NULL,
            `status`             ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
            `reject_reason`      TEXT          DEFAULT NULL,
            `created_at`         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
}

ensure_event_suggestions_table($pdo);

try {
    $pdo->exec("ALTER TABLE `events` MODIFY COLUMN `event_date` VARCHAR(100) DEFAULT NULL");
} catch (PDOException $ex) {
}

// Конфигурация разделов админ-панели
$sections = [
    'people' => [
        'title' => 'Рассказы очевидцев',
        'table' => 'people',
        'pk'    => 'id',
        'fields' => [
            'title'   => ['label' => 'Заголовок',        'type' => 'text',     'required' => true],
            'section' => ['label' => 'Очевидец / раздел','type' => 'text',     'required' => true],
            'image'   => ['label' => 'Путь к изображению','type' => 'text'],
            'content' => ['label' => 'Текст рассказа',   'type' => 'textarea', 'required' => true],
        ],
    ],
    'suggestions' => [
        'title' => 'Предложенные истории',
        'table' => 'story_suggestions',
        'pk'    => 'id',
        'has_moderation' => true,
        'fields' => [
            'suggested_by_name'  => ['label' => 'Автор предложения', 'type' => 'text',     'required' => true],
            'suggested_by_email' => ['label' => 'E-mail автора',     'type' => 'text'],
            'title'              => ['label' => 'Заголовок истории', 'type' => 'text',     'required' => true],
            'image'              => ['label' => 'Путь к изображению','type' => 'text'],
            'content'            => ['label' => 'Текст истории',     'type' => 'textarea', 'required' => true],
            'status'             => [
                'label'   => 'Статус',
                'type'    => 'select',
                'options' => [
                    'new'      => 'Новая',
                    'approved' => 'Одобрена',
                    'rejected' => 'Отклонена',
                ],
                'required' => true,
            ],
            'reject_reason'      => ['label' => 'Причина отклонения', 'type' => 'textarea'],
        ],
    ],
    'event_suggestions' => [
        'title' => 'Предложенные события',
        'table' => 'event_suggestions',
        'pk'    => 'id',
        'has_moderation' => true,
        'moderation_target' => 'events',
        'fields' => [
            'suggested_by_name'  => ['label' => 'Автор предложения', 'type' => 'text',     'required' => true],
            'suggested_by_email' => ['label' => 'E-mail автора',     'type' => 'text'],
            'title'              => ['label' => 'Название события',  'type' => 'text',     'required' => true],
            'description'        => ['label' => 'Описание',          'type' => 'textarea'],
            'event_date'         => ['label' => 'Дата события',      'type' => 'text'],
            'location'           => ['label' => 'Локация',           'type' => 'text'],
            'lat'                => ['label' => 'Широта',            'type' => 'text'],
            'lng'                => ['label' => 'Долгота',           'type' => 'text'],
            'type'               => [
                'label'   => 'Тип',
                'type'    => 'select',
                'options' => [
                    'massacre' => 'Карательная акция',
                    'camp'     => 'Лагерь',
                    'village'  => 'Сожжённая деревня',
                    'other'    => 'Другое',
                ],
                'required' => true,
            ],
            'status' => [
                'label'   => 'Статус',
                'type'    => 'select',
                'options' => [
                    'new'      => 'Новое',
                    'approved' => 'Одобрено',
                    'rejected' => 'Отклонено',
                ],
                'required' => true,
            ],
            'reject_reason' => ['label' => 'Причина отклонения', 'type' => 'textarea'],
        ],
    ],
    'victims' => [
        'title' => 'Жертвы / список погибших',
        'table' => 'victims',
        'pk'    => 'id',
        'fields' => [
            'surname'    => ['label' => 'Фамилия',      'type' => 'text',     'required' => true],
            'name'       => ['label' => 'Имя',          'type' => 'text',     'required' => true],
            'patronymic' => ['label' => 'Отчество',     'type' => 'text',     'required' => true],
            'birth_date' => ['label' => 'Дата рождения','type' => 'text'],
            'death_date' => ['label' => 'Дата смерти',  'type' => 'text'],
            'notes'      => ['label' => 'Примечание',   'type' => 'textarea'],
        ],
    ],
    'memorials' => [
        'title' => 'Мемориалы',
        'table' => 'memorials',
        'pk'    => 'id',
        'fields' => [
            'name'        => ['label' => 'Название',     'type' => 'text',     'required' => true],
            'description' => ['label' => 'Описание',     'type' => 'textarea'],
            'location'    => ['label' => 'Расположение', 'type' => 'text'],
            'lat'         => ['label' => 'Широта',       'type' => 'text'],
            'lng'         => ['label' => 'Долгота',      'type' => 'text'],
            'image'       => ['label' => 'Изображение',  'type' => 'text'],
        ],
    ],
    'events' => [
        'title' => 'События на карте',
        'table' => 'events',
        'pk'    => 'id',
        'fields' => [
            'title'       => ['label' => 'Название события', 'type' => 'text',     'required' => true],
            'description' => ['label' => 'Описание',          'type' => 'textarea'],
            'event_date'  => ['label' => 'Дата события',      'type' => 'text'],
            'location'    => ['label' => 'Локация',           'type' => 'text'],
            'lat'         => ['label' => 'Широта',            'type' => 'text'],
            'lng'         => ['label' => 'Долгота',           'type' => 'text'],
            'type'        => [
                'label'   => 'Тип',
                'type'    => 'select',
                'options' => [
                    'massacre' => 'Карательная акция',
                    'camp'     => 'Лагерь',
                    'village'  => 'Сожжённая деревня',
                    'other'    => 'Другое',
                ],
                'required' => true,
            ],
        ],
    ],
    'gallery' => [
        'title' => 'Галерея фотографий',
        'table' => 'gallery',
        'pk'    => 'id',
        'fields' => [
            'title'    => ['label' => 'Подпись к фото', 'type' => 'text',     'required' => true],
            'image'    => ['label' => 'Путь к файлу',   'type' => 'text',     'required' => true],
            'category' => ['label' => 'Категория',      'type' => 'text'],
            'year'     => ['label' => 'Год',            'type' => 'text'],
            'source'   => ['label' => 'Источник',       'type' => 'text'],
        ],
    ],
    'education' => [
        'title' => 'Образовательные материалы',
        'table' => 'education',
        'pk'    => 'id',
        'fields' => [
            'title' => ['label' => 'Заголовок',  'type' => 'text',     'required' => true],
            'body'  => ['label' => 'Содержимое','type' => 'textarea'],
            'type'  => [
                'label'   => 'Тип материала',
                'type'    => 'select',
                'options' => [
                    'article'  => 'Статья',
                    'video'    => 'Видео',
                    'document' => 'Документ',
                    'quiz'     => 'Тест / викторина',
                ],
                'required' => true,
            ],
            'url'   => ['label' => 'Внешняя ссылка','type' => 'text'],
        ],
    ],
    'timeline' => [
        'title' => 'Хронология событий',
        'table' => 'timeline',
        'pk'    => 'id',
        'fields' => [
            'year'        => ['label' => 'Год',         'type' => 'text',     'required' => true],
            'month'       => ['label' => 'Месяц (1-12)','type' => 'text'],
            'title'       => ['label' => 'Заголовок',   'type' => 'text',     'required' => true],
            'description' => ['label' => 'Описание',    'type' => 'textarea'],
            'image'       => ['label' => 'Изображение', 'type' => 'text'],
        ],
    ],
    'users' => [
        'title' => 'Управление пользователями',
        'table' => 'users',
        'pk'    => 'id',
        // В этом разделе теперь можно и создавать, и редактировать пользователей
        'allow_create' => true,
        'fields' => [
            'username'  => ['label' => 'Логин',     'type' => 'text', 'required' => true],
            'email'     => ['label' => 'E-mail',    'type' => 'text', 'required' => true],
            'role'      => [
                'label'   => 'Роль',
                'type'    => 'select',
                'options' => [
                    'user'      => 'Пользователь',
                    'moderator' => 'Модератор',
                    'admin'     => 'Администратор',
                ],
                'required' => true,
            ],
            'is_active' => [
                'label'   => 'Статус',
                'type'    => 'select',
                'options' => [
                    '1' => 'Активен',
                    '0' => 'Заблокирован',
                ],
                'required' => true,
            ],
        ],
    ],
];

$currentKey = $_GET['section'] ?? 'people';
if (!isset($sections[$currentKey])) {
    $currentKey = 'people';
}
$currentSection = $sections[$currentKey];

$message = '';
$errors  = [];
$editRow = null;

if (isset($_GET['msg']) && $_GET['msg'] !== '') {
    $message = (string) $_GET['msg'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $sectionFrom = $_POST['section'] ?? $currentKey;
    if (!isset($sections[$sectionFrom])) {
        $sectionFrom = 'people';
    }
    $currentKey      = $sectionFrom;
    $currentSection  = $sections[$currentKey];
    $table           = $currentSection['table'];
    $pk              = $currentSection['pk'];
    $id              = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$pk} = :id");
        $stmt->execute([':id' => $id]);
        $message = 'Запись удалена.';

    } elseif ($action === 'approve_story' && $id > 0 && $currentKey === 'suggestions') {
        $stmt = $pdo->prepare('SELECT * FROM story_suggestions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $suggestion = $stmt->fetch();

        if ($suggestion && $suggestion['status'] !== 'approved') {
            $pdo->prepare('UPDATE story_suggestions SET status = "approved", reject_reason = NULL WHERE id = :id')
                ->execute([':id' => $id]);

            $pdo->prepare('INSERT INTO people (title, content, image, section, user_id) VALUES (:t, :c, :i, :s, :u)')
                ->execute([
                    ':t' => $suggestion['title'],
                    ':c' => $suggestion['content'],
                    ':i' => $suggestion['image'],
                    ':s' => $suggestion['suggested_by_name'],
                    ':u' => $suggestion['user_id'],
                ]);

            if (!empty($suggestion['suggested_by_email'])) {
                send_story_approved_email(
                    $suggestion['suggested_by_email'],
                    $suggestion['suggested_by_name'],
                    $suggestion['title']
                );
            }

            $message = 'История одобрена и опубликована. Автору отправлено уведомление.';
        } else {
            $message = 'История уже была одобрена ранее.';
        }

    } elseif ($action === 'reject_story' && $id > 0 && $currentKey === 'suggestions') {
        $reason = trim($_POST['reject_reason'] ?? '');
        if ($reason === '') {
            $errors[] = 'Укажите причину отклонения.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM story_suggestions WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $suggestion = $stmt->fetch();

            if ($suggestion) {
                $pdo->prepare('UPDATE story_suggestions SET status = "rejected", reject_reason = :r WHERE id = :id')
                    ->execute([':r' => $reason, ':id' => $id]);

                if (!empty($suggestion['suggested_by_email'])) {
                    send_story_rejected_email(
                        $suggestion['suggested_by_email'],
                        $suggestion['suggested_by_name'],
                        $suggestion['title'],
                        $reason
                    );
                }

                $message = 'История отклонена. Автору отправлено письмо с причиной.';
            }
        }

    } elseif ($action === 'approve_story' && $id > 0 && $currentKey === 'event_suggestions') {
        $stmt = $pdo->prepare('SELECT * FROM event_suggestions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $suggestion = $stmt->fetch();

        if ($suggestion && $suggestion['status'] !== 'approved') {
            $pdo->prepare('UPDATE event_suggestions SET status = "approved", reject_reason = NULL WHERE id = :id')
                ->execute([':id' => $id]);

            $pdo->prepare('INSERT INTO events (title, description, event_date, location, lat, lng, type) VALUES (:t, :d, :ed, :loc, :lat, :lng, :tp)')
                ->execute([
                    ':t'   => $suggestion['title'],
                    ':d'   => $suggestion['description'],
                    ':ed'  => $suggestion['event_date'],
                    ':loc' => $suggestion['location'],
                    ':lat' => $suggestion['lat'],
                    ':lng' => $suggestion['lng'],
                    ':tp'  => $suggestion['type'],
                ]);

            if (!empty($suggestion['suggested_by_email'])) {
                send_story_approved_email(
                    $suggestion['suggested_by_email'],
                    $suggestion['suggested_by_name'],
                    $suggestion['title']
                );
            }

            $message = 'Событие одобрено и добавлено на карту. Автору отправлено уведомление.';
        } else {
            $message = 'Событие уже было одобрено ранее.';
        }

    } elseif ($action === 'reject_story' && $id > 0 && $currentKey === 'event_suggestions') {
        $reason = trim($_POST['reject_reason'] ?? '');
        if ($reason === '') {
            $errors[] = 'Укажите причину отклонения.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM event_suggestions WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $suggestion = $stmt->fetch();

            if ($suggestion) {
                $pdo->prepare('UPDATE event_suggestions SET status = "rejected", reject_reason = :r WHERE id = :id')
                    ->execute([':r' => $reason, ':id' => $id]);

                if (!empty($suggestion['suggested_by_email'])) {
                    send_story_rejected_email(
                        $suggestion['suggested_by_email'],
                        $suggestion['suggested_by_name'],
                        $suggestion['title'],
                        $reason
                    );
                }

                $message = 'Событие отклонено. Автору отправлено письмо с причиной.';
            }
        }

    } elseif (in_array($action, ['create', 'update'], true)) {
        $data = [];

        foreach ($currentSection['fields'] as $name => $meta) {
            $raw = $_POST[$name] ?? '';
            $value = is_string($raw) ? trim($raw) : $raw;

            if (!empty($meta['required']) && $value === '') {
                $errors[] = 'Поле «' . $meta['label'] . '» обязательно.';
            }

            if ($value === '' && !empty($meta['nullable'])) {
                $data[$name] = null;
            } else {
                $data[$name] = $value;
            }
        }

        if (!$errors) {
            $allowCreate = $currentSection['allow_create'] ?? true;
            if ($action === 'create') {
                if (!$allowCreate) {
                    $errors[] = 'Создание записей в этом разделе отключено.';
                    $sql = '';
                } else {
                    $columns      = array_keys($data);
                    $placeholders = array_map(static fn(string $k): string => ':' . $k, $columns);
                    $sql          = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                }
            } else {
                if ($id <= 0) {
                    $errors[] = 'Не указан ID записи для обновления.';
                    $sql = '';
                } else {
                    $assignments = array_map(static fn(string $k): string => $k . ' = :' . $k, array_keys($data));
                    $sql         = "UPDATE {$table} SET " . implode(', ', $assignments) . " WHERE {$pk} = :id";
                }
            }

            if (!$errors && $sql !== '') {
                $stmt = $pdo->prepare($sql);
                foreach ($data as $k => $v) {
                    $stmt->bindValue(':' . $k, $v === '' ? null : $v);
                }
                if ($action === 'update') {
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                }
                $stmt->execute();
                $message = $action === 'create' ? 'Запись добавлена.' : 'Запись обновлена.';
            }
        }
    }

    $pageFrom = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
    $redirectTo = '/admin.php?section=' . urlencode($currentKey) . '&page=' . $pageFrom;
    if ($message !== '') {
        $redirectTo .= '&msg=' . urlencode($message);
    }
    header('Location: ' . $redirectTo);
    exit;
}

// Получаем запись для редактирования, если нужно
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
if ($editId > 0) {
    $table = $currentSection['table'];
    $pk    = $currentSection['pk'];
    $stmt  = $pdo->prepare("SELECT * FROM {$table} WHERE {$pk} = :id");
    $stmt->execute([':id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

// Список записей для текущего раздела (с пагинацией)
$table   = $currentSection['table'];
$pk      = $currentSection['pk'];
$fields  = array_keys($currentSection['fields']);
$selectColumns = $pk . ', ' . implode(', ', $fields);

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$countStmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT {$selectColumns} FROM {$table} ORDER BY {$pk} DESC LIMIT :lim OFFSET :off");
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// Дополнительная подстраховка для раздела пользователей:
// если по какой-то причине пагинация вернула пустой список,
// пробуем ещё раз без LIMIT, чтобы всегда показать хотя бы текущих пользователей.
if ($currentKey === 'users' && !$rows && $totalRows > 0) {
    $stmt = $pdo->query("SELECT {$selectColumns} FROM {$table} ORDER BY {$pk} DESC");
    $rows = $stmt->fetchAll();
    $totalPages = 1;
    $page = 1;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            background-color: #0e0e0e;
            color: #f0f0f0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.5;
        }
        a { color: #ffb74d; text-decoration: none; }
        a:hover { text-decoration: underline; }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 260px;
            padding: 22px 18px;
            background: rgba(10,10,10,0.96);
            border-right: 1px solid #262626;
            box-shadow: 4px 0 24px rgba(0,0,0,0.7);
            box-sizing: border-box;
        }

        .admin-sidebar-title {
            font-size: 18px;
            margin: 0 0 4px;
        }
        .admin-sidebar-subtitle {
            font-size: 12px;
            color: #9e9e9e;
            margin: 0 0 16px;
        }
        .admin-user {
            font-size: 13px;
            margin-bottom: 14px;
        }
        .admin-sidebar-links {
            font-size: 13px;
            margin-bottom: 20px;
        }
        .admin-sidebar-links a {
            margin-right: 10px;
        }

        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-nav-item {
            margin-bottom: 4px;
        }
        .admin-nav-link {
            display: block;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 13px;
            color: #e0e0e0;
            background: transparent;
            border: 1px solid transparent;
            transition: background 0.15s, border-color 0.15s, transform 0.08s;
        }
        .admin-nav-link:hover {
            background: #1c1c1c;
            border-color: #333;
            transform: translateX(2px);
            text-decoration: none;
        }
        .admin-nav-link.active {
            background: linear-gradient(135deg, #ff7043, #ff5722);
            border-color: #ff5722;
            color: #fff;
            box-shadow: 0 8px 18px rgba(255,87,34,0.5);
        }

        .admin-main {
            flex: 1;
            padding: 26px 30px 34px;
            box-sizing: border-box;
        }
        .admin-breadcrumb {
            font-size: 12px;
            color: #9e9e9e;
            margin-bottom: 4px;
        }
        .admin-heading {
            margin: 0 0 18px;
            font-size: 22px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(320px, 1fr);
            gap: 20px;
            align-items: flex-start;
        }
        /* В разделе пользователей форма и список идут столбиком, чтобы всё было видно */
        .admin-grid-users {
            display: block;
        }
        .admin-grid-users .card {
            margin-bottom: 20px;
        }

        .card {
            background: rgba(15,15,15,0.96);
            border-radius: 14px;
            padding: 18px 18px 20px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.8);
            border: 1px solid #262626;
            box-sizing: border-box;
        }
        .card h2 {
            margin: 0 0 8px;
            font-size: 17px;
        }
        .card p.card-subtitle {
            margin: 0 0 14px;
            font-size: 13px;
            color: #aaaaaa;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #ff7043;
        }

        .field-input,
        .field-textarea,
        .field-select {
            width: 100%;
            padding: 9px 11px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #444;
            background: #101010;
            color: #f0f0f0;
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.18s, box-shadow 0.18s, background-color 0.18s;
        }
        .field-textarea {
            min-height: 110px;
            resize: vertical;
        }
        .field-input:focus,
        .field-textarea:focus,
        .field-select:focus {
            border-color: #ff5722;
            box-shadow: 0 0 0 2px rgba(255,87,34,0.32);
            background: #151515;
        }

        .form-actions {
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            transition: transform 0.09s, box-shadow 0.12s, filter 0.12s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #ff7043, #ff5722);
            color: #fff;
        }
        .btn-primary:hover {
            filter: brightness(1.04);
            box-shadow: 0 10px 20px rgba(255,87,34,0.55);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: none;
        }
        .btn-secondary {
            background: #272727;
            color: #e0e0e0;
            border: 1px solid #3a3a3a;
        }
        .btn-secondary:hover {
            background: #333333;
        }
        .btn-danger {
            background: #c62828;
            color: #fff;
        }
        .btn-danger:hover {
            background: #b71c1c;
            box-shadow: 0 10px 20px rgba(198,40,40,0.6);
        }

        .message {
            margin-bottom: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            background: rgba(46,125,50,0.8);
            border: 1px solid #66bb6a;
            font-size: 13px;
        }
        .errors {
            margin-bottom: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            background: rgba(183,28,28,0.85);
            border: 1px solid #ef5350;
            font-size: 13px;
        }
        .errors ul {
            margin: 0;
            padding-left: 18px;
        }

        .table-card {
            margin-top: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            padding: 6px 8px;
            border-bottom: 1px solid #262626;
            vertical-align: top;
        }
        th {
            text-align: left;
            font-weight: 500;
            color: #bdbdbd;
            background: #181818;
        }
        tr:nth-child(even) td {
            background: rgba(12,12,12,0.9);
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            background: #424242;
            font-size: 11px;
        }
        .badge-status-new { background: #424242; }
        .badge-status-approved { background: #2e7d32; }
        .badge-status-rejected { background: #b71c1c; }
        .badge-user-active { background: #2e7d32; }
        .badge-user-blocked { background: #b71c1c; }
        .badge-role-admin { background: linear-gradient(135deg,#ff7043,#ff5722); }
        .badge-role-moderator { background: #1565c0; }
        .badge-role-user { background: #616161; }

        .table-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .text-muted {
            color: #9e9e9e;
        }
        .truncate {
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-approve {
            background: #2e7d32;
            color: #fff;
        }
        .btn-approve:hover {
            background: #388e3c;
            box-shadow: 0 8px 18px rgba(46,125,50,0.6);
        }
        .btn-reject {
            background: #6a1b9a;
            color: #fff;
        }
        .btn-reject:hover {
            background: #7b1fa2;
            box-shadow: 0 8px 18px rgba(106,27,154,0.6);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal-card {
            background: #1e1e1e;
            border: 1px solid #333;
            border-radius: 14px;
            padding: 22px 24px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.9);
        }
        .modal-card h3 {
            margin: 0 0 6px;
            font-size: 17px;
        }
        .modal-card .modal-subtitle {
            margin: 0 0 14px;
            font-size: 13px;
            color: #aaa;
        }
        .modal-card .modal-actions {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        @media (max-width: 960px) {
            .admin-shell {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
                box-shadow: 0 4px 14px rgba(0,0,0,0.9);
                border-right: none;
                border-bottom: 1px solid #262626;
                padding: 16px 14px;
            }
            .admin-nav {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }
            .admin-main {
                padding: 18px 14px 26px;
            }
            .admin-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 600px) {
            .admin-heading { font-size: 18px; }
            .admin-sidebar-title { font-size: 15px; }
            .admin-nav-link { font-size: 12px; padding: 6px 8px; }
            .card { padding: 14px 12px; }
            .card h2 { font-size: 14px; }
            label { font-size: 11px; }
            .field-input, .field-textarea, .field-select { font-size: 12px; padding: 8px 10px; }
            .btn { font-size: 11px; padding: 6px 10px; }
            table { font-size: 11px; }
            th, td { padding: 4px 6px; }
            .modal-card { padding: 16px 14px; margin: 0 10px; }
            .pagination a, .pagination span { min-width: 28px; height: 28px; font-size: 11px; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h1 class="admin-sidebar-title">Админ-панель</h1>
        <p class="admin-sidebar-subtitle">Управление данными сайта «Геноцид в Беларуси»</p>

        <div class="admin-user">
            Вы вошли как <strong><?= e($currentUser['username']) ?></strong><br>
            <span class="text-muted">Роль: <?= e($currentUser['role']) ?></span>
        </div>

        <div class="admin-sidebar-links">
            <a href="/index.html">← На сайт</a>
            <a href="/profile.php">Профиль</a>
            <a href="/logout.php">Выйти</a>
        </div>

        <ul class="admin-nav">
            <?php foreach ($sections as $key => $section): ?>
                <li class="admin-nav-item">
                    <a
                        class="admin-nav-link <?= $key === $currentKey ? 'active' : '' ?>"
                        href="/admin.php?section=<?= urlencode($key) ?>&page=1"
                    >
                        <?= e($section['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="admin-export" style="margin-top:20px;padding-top:16px;border-top:1px solid #333;">
            <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Экспорт</div>
            <a href="/admin_export.php?type=victims" class="admin-nav-link" target="_blank" style="margin-bottom:4px;">Список жертв (PDF/CSV)</a>
            <a href="/admin_export.php?type=people" class="admin-nav-link" target="_blank">Истории (PDF/CSV)</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-breadcrumb">
            Раздел: <?= e($currentSection['title']) ?>
        </div>
        <h2 class="admin-heading"><?= e($currentSection['title']) ?></h2>

        <?php if ($message): ?>
            <div class="message"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-grid <?= $currentKey === 'users' ? 'admin-grid-users' : '' ?>">
            <section class="card">
                <h2><?= $editRow ? 'Редактирование записи' : 'Новая запись' ?></h2>
                <p class="card-subtitle">
                    <?= $editRow ? 'Измените данные и сохраните изменения.' : 'Заполните форму, чтобы добавить новую запись в раздел.' ?>
                </p>

                <form method="post">
                    <input type="hidden" name="section" value="<?= e($currentKey) ?>">
                    <input type="hidden" name="action" value="<?= $editRow ? 'update' : 'create' ?>">
                    <input type="hidden" name="page" value="<?= (int)$page ?>">
                    <?php if ($editRow): ?>
                        <input type="hidden" name="id" value="<?= (int) $editRow[$currentSection['pk']] ?>">
                    <?php endif; ?>

                    <?php foreach ($currentSection['fields'] as $name => $meta): ?>
                        <?php
                            $label = $meta['label'] ?? $name;
                            $type  = $meta['type'] ?? 'text';
                            $value = $editRow[$name] ?? '';
                        ?>
                        <label for="<?= e($name) ?>"><?= e($label) ?></label>

                        <?php if ($type === 'textarea'): ?>
                            <textarea
                                class="field-textarea"
                                id="<?= e($name) ?>"
                                name="<?= e($name) ?>"
                                <?= !empty($meta['required']) ? 'required' : '' ?>
                            ><?= e((string) $value) ?></textarea>
                        <?php elseif ($type === 'select'): ?>
                            <select
                                class="field-select"
                                id="<?= e($name) ?>"
                                name="<?= e($name) ?>"
                                <?= !empty($meta['required']) ? 'required' : '' ?>
                            >
                                <?php foreach ($meta['options'] as $optValue => $optLabel): ?>
                                    <option value="<?= e($optValue) ?>" <?= (string)$value === (string)$optValue ? 'selected' : '' ?>>
                                        <?= e($optLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input
                                class="field-input"
                                type="<?= $type === 'date' ? 'date' : 'text' ?>"
                                id="<?= e($name) ?>"
                                name="<?= e($name) ?>"
                                value="<?= e((string) $value) ?>"
                                <?= !empty($meta['required']) ? 'required' : '' ?>
                            >
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?= $editRow ? 'Сохранить' : 'Добавить' ?>
                        </button>
                        <?php if ($editRow): ?>
                            <a href="/admin.php?section=<?= urlencode($currentKey) ?>" class="btn btn-secondary">Отмена</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="card table-card">
                <h2>Список записей</h2>
                <p class="card-subtitle">
                    Показаны последние записи (до 200). Вы можете отредактировать или удалить любую.
                </p>

                <?php if (!$rows): ?>
                    <p class="text-muted">Пока нет записей в этом разделе.</p>
                <?php else: ?>
                    <div style="max-height: 520px; overflow: auto; -webkit-overflow-scrolling: touch;">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <?php foreach ($currentSection['fields'] as $name => $meta): ?>
                                    <th><?= e($meta['label'] ?? $name) ?></th>
                                <?php endforeach; ?>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?= (int) $row[$pk] ?></td>
                                    <?php foreach ($currentSection['fields'] as $name => $meta): ?>
                                        <?php
                                            $type   = $meta['type'] ?? 'text';
                                            $value  = $row[$name] ?? '';
                                            $output = (string) $value;
                                            if ($type === 'textarea' && mb_strlen($output) > 120) {
                                                $output = mb_substr($output, 0, 120) . '…';
                                            }
                                        ?>
                                        <td class="<?= $type === 'textarea' ? 'truncate' : '' ?>">
                                            <?php if ($name === 'status'): ?>
                                                <?php
                                                    $badgeClass = 'badge-status-' . $value;
                                                    $label = $meta['options'][$value] ?? $value;
                                                ?>
                                                <span class="badge <?= e($badgeClass) ?>"><?= e((string)$label) ?></span>
                                            <?php elseif ($currentKey === 'users' && $name === 'is_active'): ?>
                                                <?php
                                                    $isActive = (string)$value === '1';
                                                    $badgeClass = $isActive ? 'badge-user-active' : 'badge-user-blocked';
                                                    $label = $isActive ? 'Активен' : 'Заблокирован';
                                                ?>
                                                <span class="badge <?= e($badgeClass) ?>"><?= e($label) ?></span>
                                            <?php elseif ($currentKey === 'users' && $name === 'role'): ?>
                                                <?php
                                                    $roleKey = (string)$value;
                                                    $badgeClass = 'badge-role-' . $roleKey;
                                                    $label = $meta['options'][$roleKey] ?? $roleKey;
                                                ?>
                                                <span class="badge <?= e($badgeClass) ?>"><?= e((string)$label) ?></span>
                                            <?php else: ?>
                                                <?= e($output) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <div class="table-actions">
                                            <a
                                                class="btn btn-secondary"
                                                href="/admin.php?section=<?= urlencode($currentKey) ?>&edit=<?= (int) $row[$pk] ?>"
                                            >Ред.</a>

                                            <form
                                                action="/admin.php"
                                                method="post"
                                                onsubmit="return confirm('Удалить эту запись безвозвратно?');"
                                            >
                                                <input type="hidden" name="section" value="<?= e($currentKey) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int) $row[$pk] ?>">
                                                <input type="hidden" name="page" value="<?= (int)$page ?>">
                                                <button type="submit" class="btn btn-danger">Удалить</button>
                                            </form>

                                            <?php if (!empty($currentSection['has_moderation']) && ($row['status'] ?? '') === 'new'): ?>
                                                <form action="/admin.php" method="post">
                                                    <input type="hidden" name="section" value="<?= e($currentKey) ?>">
                                                    <input type="hidden" name="action" value="approve_story">
                                                    <input type="hidden" name="id" value="<?= (int) $row[$pk] ?>">
                                                    <button type="submit" class="btn btn-approve" onclick="return confirm('Одобрить и опубликовать?');">Одобрить</button>
                                                </form>

                                                <button
                                                    type="button"
                                                    class="btn btn-reject"
                                                    onclick="openRejectModal(<?= (int) $row[$pk] ?>, <?= e(json_encode($row['title'] ?? '', JSON_UNESCAPED_UNICODE)) ?>, <?= e(json_encode($currentKey)) ?>)"
                                                >Отклонить</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div style="margin-top:12px; display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                            <?php if ($page > 1): ?>
                                <a href="/admin.php?section=<?= urlencode($currentKey) ?>&page=<?= $page - 1 ?>" class="btn btn-secondary">&laquo; Назад</a>
                            <?php endif; ?>
                            <?php
                                $start = max(1, $page - 3);
                                $end   = min($totalPages, $page + 3);
                                for ($p = $start; $p <= $end; $p++):
                            ?>
                                <?php if ($p === $page): ?>
                                    <span class="btn btn-secondary" style="background:#ff5722;border-color:#ff5722;"><?= $p ?></span>
                                <?php else: ?>
                                    <a href="/admin.php?section=<?= urlencode($currentKey) ?>&page=<?= $p ?>" class="btn btn-secondary"><?= $p ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="/admin.php?section=<?= urlencode($currentKey) ?>&page=<?= $page + 1 ?>" class="btn btn-secondary">Вперёд &raquo;</a>
                            <?php endif; ?>
                            <span class="text-muted" style="margin-left:auto;">Страница <?= $page ?> из <?= $totalPages ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>

<div class="modal-overlay" id="rejectModal">
    <div class="modal-card">
        <h3 id="rejectModalHeading">Отклонить</h3>
        <p class="modal-subtitle" id="rejectModalTitle"></p>
        <form action="/admin.php" method="post" id="rejectForm">
            <input type="hidden" name="section" id="rejectModalSection" value="suggestions">
            <input type="hidden" name="action" value="reject_story">
            <input type="hidden" name="id" id="rejectModalId" value="">
            <label for="reject_reason">Причина отклонения</label>
            <textarea
                class="field-textarea"
                id="reject_reason"
                name="reject_reason"
                required
                placeholder="Опишите причину отклонения..."
                style="min-height:90px;"
            ></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Отмена</button>
                <button type="submit" class="btn btn-reject">Отклонить и отправить письмо</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id, title, section) {
    section = section || 'suggestions';
    var isEvent = (section === 'event_suggestions');
    var label = isEvent ? 'событие' : 'историю';
    document.getElementById('rejectModalId').value = id;
    document.getElementById('rejectModalSection').value = section;
    document.getElementById('rejectModalHeading').textContent = 'Отклонить ' + label;
    document.getElementById('rejectModalTitle').textContent = 'Вы отклоняете ' + label + ' «' + title + '». Укажите причину — она будет отправлена автору по e-mail.';
    document.getElementById('reject_reason').value = '';
    document.getElementById('rejectModal').classList.add('open');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('open');
}
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>

</body>
</html>

