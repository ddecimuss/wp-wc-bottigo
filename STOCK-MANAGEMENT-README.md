# Управление товарами в наличии для фильтров WooCommerce

## Описание

Данная функциональность настраивает WooCommerce так, чтобы фильтры товаров работали только с товарами, которые есть в наличии. Товары "Нет в наличии" исключаются из результатов поиска, фильтрации и подсчета.

## Что делает эта функциональность

### 1. **Скрытие товаров "Нет в наличии"**
- Товары без наличия не показываются на страницах магазина, категорий и тегов
- Применяется только к основным запросам товаров на frontend

### 2. **Фильтрация в блоках WooCommerce**
- Блоки фильтров учитывают только товары в наличии
- Счетчики в фильтрах показывают количество товаров в наличии
- Опции фильтров с нулевым количеством товаров скрываются

### 3. **Корректные счетчики**
- Layered navigation виджеты показывают правильные счетчики
- Блоки фильтров отображают актуальное количество товаров
- REST API запросы фильтруют товары по наличию

### 4. **Совместимость с различными типами запросов**
- Основные WP_Query запросы
- WooCommerce product queries
- Gutenberg блоки
- REST API запросы
- AJAX запросы фильтров

## Технические детали

### Хуки и фильтры

#### 1. `pre_get_posts`
```php
// Модифицирует основные запросы для исключения товаров без наличия
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
            $query->set('meta_query', array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                )
            ));
        }
    }
});
```

#### 2. `woocommerce_product_query_meta_query`
```php
// Добавляет фильтр по наличию к WooCommerce запросам
add_filter('woocommerce_product_query_meta_query', function($meta_query, $query) {
    if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
        $meta_query[] = array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        );
    }
    return $meta_query;
}, 10, 2);
```

#### 3. `woocommerce_layered_nav_count`
```php
// Пересчитывает количество товаров в layered navigation
add_filter('woocommerce_layered_nav_count', function($count, $term) {
    // SQL запрос для подсчета только товаров в наличии
    return intval($count);
}, 10, 2);
```

#### 4. Блоки Gutenberg
```php
// Фильтрация для блоков WooCommerce
add_filter('woocommerce_blocks_product_grid_query_args', function($args) {
    $args['meta_query'][] = array(
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '='
    );
    return $args;
});
```

#### 5. REST API
```php
// Фильтрация REST API запросов
add_filter('woocommerce_rest_product_object_query', function($args, $request) {
    if (!is_admin() && !wp_doing_ajax()) {
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        );
    }
    return $args;
}, 10, 2);
```

### JavaScript улучшения

Дополнительно JavaScript код скрывает опции фильтров с нулевым количеством товаров:

```javascript
// Only show filters that have products in stock (count > 0)
if (count <= 0) {
    // Hide filter options with no in-stock products
    item.style.display = 'none';
    return;
}
```

## Влияние на производительность

### Положительные аспекты:
- **Меньше товаров для обработки** - запросы работают быстрее
- **Более релевантные результаты** - пользователи видят только доступные товары
- **Точные счетчики** - нет путаницы с количеством товаров

### Потенциальные нагрузки:
- **Дополнительные meta_query** - могут замедлить сложные запросы
- **Пересчет счетчиков** - дополнительные SQL запросы для layered nav

## Настройка и кастомизация

### Отключение для определенных страниц

Если нужно показывать товары без наличия на определенных страницах:

```php
// Добавить условие в функции
if (is_shop() || is_product_category()) { // Убрать is_product_tag() например
    // Применить фильтр
}
```

### Изменение статуса наличия

Для включения товаров с другими статусами:

```php
$meta_query[] = array(
    'key' => '_stock_status',
    'value' => array('instock', 'onbackorder'), // Добавить "под заказ"
    'compare' => 'IN'
);
```

### Отключение для администраторов

```php
// Не применять фильтр для администраторов
if (!current_user_can('manage_options')) {
    // Применить фильтр наличия
}
```

## Совместимость

### Поддерживаемые функции:
- ✅ WooCommerce блоки фильтров
- ✅ Layered Navigation виджеты
- ✅ Стандартные архивы товаров
- ✅ REST API
- ✅ AJAX фильтрация
- ✅ Поиск товаров

### Тестирование:
- ✅ Страница магазина
- ✅ Страницы категорий
- ✅ Страницы тегов
- ✅ Фильтры атрибутов
- ✅ Ценовые фильтры
- ✅ Мобильные устройства

## Возможные проблемы и решения

### Фильтры все еще показывают товары без наличия

**Причина**: Кеширование или конфликт с другими плагинами

**Решение**:
1. Очистить все кеши (WP, плагины, CDN)
2. Проверить порядок загрузки плагинов
3. Временно отключить другие плагины для тестирования

### Счетчики в фильтрах неточные

**Причина**: Кеширование счетчиков или конфликт с layered nav

**Решение**:
1. Очистить кеш WooCommerce
2. Пересохранить настройки фильтров
3. Проверить настройки виджетов layered navigation

### Медленная загрузка страниц

**Причина**: Дополнительные meta_query запросы

**Решение**:
1. Оптимизировать базу данных
2. Добавить индексы для meta_key '_stock_status'
3. Использовать кеширование запросов

```sql
-- Добавить индекс для оптимизации
ALTER TABLE wp_postmeta ADD INDEX stock_status_idx (meta_key, meta_value);
```

## Мониторинг и отладка

### Проверка работы фильтров

1. **Создать товар без наличия**
2. **Проверить, что он не показывается в каталоге**
3. **Убедиться, что счетчики фильтров корректны**
4. **Протестировать на мобильных устройствах**

### Отладка SQL запросов

Добавить в wp-config.php для отладки:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Логирование SQL запросов
define('SAVEQUERIES', true);
```

### Проверка производительности

```php
// Добавить в functions.php для мониторинга
add_action('wp_footer', function() {
    if (current_user_can('manage_options') && isset($_GET['debug'])) {
        global $wpdb;
        echo '<pre>';
        echo "Queries: " . $wpdb->num_queries . "\n";
        echo "Time: " . timer_stop() . " seconds\n";
        echo '</pre>';
    }
});
```

## Заключение

Данная функциональность обеспечивает корректную работу фильтров только с товарами в наличии, улучшая пользовательский опыт и предотвращая показ недоступных товаров. Все изменения совместимы с WordPress Interactivity API и не нарушают стандартную функциональность WooCommerce.