# Mytory Docs

파일 시스템 기반 개인용 마크다운 문서 뷰어 & 에디터. 데이터베이스 없이 디렉토리 구조 자체를 콘텐츠 저장소로 사용합니다.

![PHP](https://img.shields.io/badge/PHP-≥8.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)
![Tests](https://img.shields.io/badge/tests-64%20passed-brightgreen)

## 특징

- **Zero Database** — SQLite 검색 인덱스를 제외하면 DB가 전혀 없습니다. 디렉토리 = 카테고리, 파일 = 문서.
- **실시간 검색** — SQLite FTS5 기반 전체 텍스트 검색. `fswatch`/`inotifywait`로 외부 편집도 자동 색인.
- **CodeMirror 6 에디터** — 문법 강조, 자동 저장, Ctrl+S, 다크 테마.
- **Jekyll 호환** — YAML front matter (`---` 블록)를 파싱하여 제목, 날짜, 태그 표시.
- **Tailwind CSS** — 번들 32KB. 다크모드 지원. 인쇄 스타일 포함.
- **인코딩 자동 감지** — UTF-8, EUC-KR 문서 모두 열람 가능. Windows CP949 파일명 지원.

## 빠른 시작

```bash
# 1. 클론
git clone https://github.com/mytory/mytory-docs.git
cd mytory-docs

# 2. 의존성 설치
composer install
npm install && npm run build

# 3. 설정
cp config.sample.php config.php
# → config.php를 열어 doc_roots(문서 디렉토리)를 설정하세요.

# 4. 실행
./run.sh
# → http://localhost:1111
```

## 설정

`config.php`에서 다음 항목을 설정합니다:

```php
// 문서 루트: 별칭 → 절대경로
$doc_roots = [
    '문서' => '/Users/mytory/Documents',
    '블로그' => '/Users/mytory/blog',
];

// 마크다운으로 렌더링할 확장자
$markdown_ext_list = ['md', 'txt'];

// OS 인코딩 (macOS/Linux: utf-8, Windows: CP949)
const OS_ENCODING = 'utf-8';

// 타임존
$timezone = 'Asia/Seoul';
```

## 사용법

| URL | 기능 |
|-----|------|
| `/` | 모든 doc_root 목록 |
| `/list/문서` | 디렉토리 탐색 |
| `/list/문서/하위/경로` | 하위 디렉토리 탐색 |
| `/view/문서/파일.md` | 마크다운 뷰어 |
| `/edit/문서/파일.md` | CodeMirror 6 에디터 |
| `/search?q=검색어` | 전체 검색 |
| `/search?q=검색어&root=문서` | 특정 doc_root만 검색 |
| `/api/search?q=검색어` | JSON 검색 API |

### 키보드 단축키

| 키 | 기능 |
|----|------|
| `Ctrl+S` / `Cmd+S` | 즉시 저장 |
| `/` | 검색창으로 포커스 이동 (에디터 외) |

## 검색

### 자동 인덱싱 (첫 검색 시)

검색 페이지에 처음 접근하면 모든 doc_root를 스캔하여 SQLite FTS5 인덱스를 자동 생성합니다. 인덱스 파일은 `search.db`입니다.

### 실시간 인덱싱 (외부 편집 감지)

VSCode 등 외부 에디터로 문서를 수정할 경우, 파일 변경을 감지하여 자동으로 인덱스를 갱신합니다:

```bash
# 포그라운드
./scripts/index-watch.sh

# 백그라운드 데몬
./scripts/index-watchd.sh
./scripts/stop-index-watch.sh   # 중지
```

사전 설치:

```bash
# macOS
brew install fswatch

# Linux
apt install inotify-tools
```

### Supervisor (시스템 서비스로 등록)

```bash
cp scripts/supervisor-indexer.conf /etc/supervisor/conf.d/mytory-docs-indexer.conf
# → 경로를 실제 설치 위치로 수정
supervisorctl reread && supervisorctl update
```

### CLI 명령

```bash
php scripts/indexer.php rebuild      # 전체 재색인
php scripts/indexer.php update /path/to/file.md  # 단일 파일 갱신
php scripts/indexer.php delete /path/to/file.md  # 인덱스에서 제거
```

## YAML Front Matter

마크다운 파일 상단에 메타데이터를 작성하면 뷰어에서 표시됩니다:

```yaml
---
title: React Hooks 정리
date: 2024-03-15
tags: [react, javascript]
---
```

날짜 추출 우선순위: YAML `date:` → 본문 `date:` / `날짜:` → 파일 생성일.

## 개발

### 디렉토리 구조

```
├── public/               # 웹 루트
│   ├── index.php         # 라우터 + 모든 경로 핸들러
│   └── assets/
│       ├── app.css       # Tailwind 소스
│       ├── build.css     # 컴파일된 CSS (gitignored)
│       └── app.js        # 프론트엔드 JS
├── src/                  # PHP 클래스
│   ├── bootstrap.php     # 환경 초기화
│   ├── PathParser.php    # 경로 문자열 파싱
│   ├── FrontMatter.php   # YAML 머리말 파싱
│   ├── FileUtils.php     # 파일 읽기/쓰기/목록
│   ├── MarkdownRenderer.php  # league/commonmark 래퍼
│   ├── Fts5Index.php     # SQLite FTS5 검색
│   └── views/            # PHP 템플릿
├── scripts/              # CLI 도구
│   ├── indexer.php
│   ├── index-watch.sh
│   └── supervisor-indexer.conf
├── tests/                # PHPUnit 테스트
├── config.php            # 사용자 설정 (gitignored)
├── config.sample.php     # 설정 템플릿
└── CONTEXT.md            # 도메인 용어집
```

### 테스트

```bash
php vendor/bin/phpunit
```

### CSS 빌드

```bash
npm run build             # 1회 빌드
npm run watch             # 실시간 감시
```

## Apache / Nginx 배포

PHP 빌트인 서버는 개발용입니다. 상시 운영은 Apache + PHP-FPM을 권장합니다.

### 1. Apache + mod_rewrite

```apache
<VirtualHost *:80>
    ServerName docs.local
    DocumentRoot /path/to/mytory-docs/public

    <Directory /path/to/mytory-docs/public>
        Require all granted
        AllowOverride All
        FallbackResource /index.php
    </Directory>

    # PHP-FPM
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/mytory-docs-error.log
    CustomLog ${APACHE_LOG_DIR}/mytory-docs-access.log combined
</VirtualHost>
```

`public/.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

### 2. Nginx + PHP-FPM

```nginx
server {
    listen 80;
    server_name docs.local;
    root /path/to/mytory-docs/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 검색 인덱스, 백업 디렉토리 접근 차단
    location ~ /\.(search\.db|git) {
        deny all;
    }
}
```

### 3. Search index watcher 자동 시작

검색 인덱스를 항상 최신으로 유지하려면:

```bash
# Supervisor (권장)
cp scripts/supervisor-indexer.conf /etc/supervisor/conf.d/mytory-docs-indexer.conf
# → 경로를 실제 설치 경로로 수정
supervisorctl reread && supervisorctl update

# 또는 systemd
cat > /etc/systemd/system/mytory-docs-indexer.service << 'EOF'
[Unit]
Description=Mytory Docs Index Watcher
After=network.target

[Service]
Type=simple
User=mytory
ExecStart=/path/to/mytory-docs/scripts/index-watch.sh
Restart=always

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable --now mytory-docs-indexer
```

## 마이그레이션 (v1 → v2)

v1 (master 브랜치)과 v2 (modernize 브랜치)의 주요 차이:

| v1 | v2 |
|----|----|
| Bootstrap 3 + jQuery | Tailwind CSS (jQuery 없음) |
| `<textarea>` | CodeMirror 6 |
| 1초 폴링 자동저장 | debounced fetch + Ctrl+S |
| ParsedownExtra (2018) | league/commonmark (active) |
| Symfony/Yaml | 자체 정규식 파서 |
| 검색 없음 | SQLite FTS5 검색 |
| `config.php` (글로벌 변수) | `config.php` (같은 포맷, 호환) |

`config.php`의 `$doc_roots`와 `$markdown_ext_list`는 v1과 동일한 형식이므로 그대로 사용할 수 있습니다. `$css_list`, `$js_list`, `$template` 항목은 v2에서 제거되었습니다.

## 라이선스

MIT
