# Laravel Todo List

使用 Laravel、MySQL 與 Docker 建立的待辦事項管理系統。

## 功能

* 新增待辦事項
* 編輯待辦事項
* 刪除待辦事項
* 切換完成 / 未完成狀態
* 使用 MySQL 儲存資料
* 使用 Git 進行版本控制

## 使用技術

* PHP
* Laravel
* MySQL
* Docker / Laravel Sail
* Git
* Blade
* HTML / CSS

## 安裝與啟動方式

1. 複製專案

```bash
git clone https://github.com/June2803/laravel-todo-list.git
cd laravel-todo-list
```

2. 啟動 Docker / Laravel Sail

```bash
./vendor/bin/sail up -d
```

3. 建立資料表

```bash
./vendor/bin/sail artisan migrate
```

4. 開啟網站

```text
http://localhost
```

## 專案架構簡述

* `routes/web.php`：設定頁面路由
* `app/Models/Todo.php`：Todo 資料模型
* `app/Http/Controllers/TodoController.php`：CRUD 控制邏輯
* `database/migrations/`：資料表結構設定
* `resources/views/todos/index.blade.php`：Todo List 畫面
* `public/css/todo.css`：頁面樣式
* `public/icons/`：圖示素材

## 圖示來源

- Pencil icons created by Ilham Fitrotul Hayat - Flaticon
- Delete icons created by Ilham Fitrotul Hayat - Flaticon
