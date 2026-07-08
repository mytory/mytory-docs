# Mytory Docs

파일 시스템을 데이터베이스로 쓰는 개인용 마크다운 문서 도구.

디렉토리 = 카테고리, 파일 = 문서. 별도 DB 설치가 필요 없고, `config.php`에 폴더 경로만 지정하면 바로 쓸 수 있다.

![PHP](https://img.shields.io/badge/PHP-%E2%89%A58.1-777bb4)
![License](https://img.shields.io/badge/license-MIT-blue)
![Tests](https://img.shields.io/badge/tests-65%20passed-brightgreen)

---

## 철학

이 도구는 **나 자신을 위해** 만들었다.  
다른 사람을 위한 제품이 아니다. 그래서 들어가지 않은 기능이 많다.

**복잡성은 제거하고, 필요한 것만 남겼다.**

- DB가 필요하지 않다면 넣지 않는다. 파일 시스템이 가장 단순하고 오래가는 저장소다.
- 인증은 앱 레벨에서 구현하지 않는다. 필요하면 nginx 앞단에서 처리할 일이다.
- 라이브러리는 최소한으로. YAML 파서는 정규식 22줄이면 충분하다.
- 외부 에디터로 작업하는 흐름을 방해하지 않는다. 앱은 **뷰어와 브라우저** 역할에 집중한다.
- 한국어 문서를 1급 시민으로 대접한다. `word-break: keep-all`, `~`는 범위 표시일 뿐 strikethrough가 아니다.

**10년 전 코드도 지금 다시 짜면 이렇게.**  
실용적인 선택만 남기고, 유행을 좇지 않는다.

---

## 빠른 시작

```bash
git clone git@github.com:mytory/mytory-docs.git
cd mytory-docs
composer install
npm install && npm run build
cp config.sample.php config.php
# → config.php 열어서 doc_roots 설정
./run.sh
# → http://localhost:1111
```

---

## 사용법

| URL | 설명 |
|-----|------|
| `/` | 등록된 모든 문서 루트 목록 |
| `/list/문서` | 디렉토리 탐색 (파일 목록, 날짜순 정렬) |
| `/view/문서/경로/파일.md` | 마크다운 뷰어 (각주, 표, 이미지 프록시, 제목 번호 자동 감지) |
| `/edit/문서/경로/파일.md` | CodeMirror 6 에디터 (다크 테마, 자동 저장) |
| `/search?q=리액트` | 전체 텍스트 검색 |
| `/search?q=리액트&root=문서` | 특정 루트로 범위 좁혀 검색 |
| `/api/search?q=리액트` | JSON API |

단축키: `Ctrl+S` 저장, `/` 검색창 포커스.

---

## 설정 (`config.php`)

```php
if (!defined('OS_ENCODING')) { define('OS_ENCODING', 'utf-8'); }

$doc_roots = [
    '문서' => '/Users/mytory/Documents',
    '블로그' => '/Users/mytory/blog',
];

$markdown_ext_list = ['md', 'txt'];
$timezone = 'Asia/Seoul';
```

v1과 완전히 호환된다. `$doc_roots`와 `$markdown_ext_list`는 그대로 쓰면 되고, `$css_list` `$js_list` `$template`은 v2에서 사라졌다.

---

## YAML Front Matter

마크다운 파일 상단에 메타데이터를 작성하면 뷰어가 제목, 날짜, 태그를 뽑아서 보여 준다.

```yaml
---
title: React Hooks 정리
date: 2024-03-15
tags: [react, javascript]
---
```

파서는 외부 라이브러리 없이 정규식으로 직접 구현했다. title, date, tags처럼 단순한 key-value와 리스트만 필요하기 때문이다.

---

## 검색

SQLite FTS5를 쓴다. 파일 하나(`search.db`)만 생성되고 DB 서버는 필요 없다.

### CLI 인덱싱

```bash
php scripts/indexer.php rebuild       # 전체 인덱싱
php scripts/indexer.php update 파일    # 단일 파일 갱신
```

### 실시간 인덱스 감시

외부 에디터(VS Code 등)로 파일을 수정하면 자동 반영한다.

```bash
# 포그라운드
./scripts/index-watch.sh

# 데몬
./scripts/index-watchd.sh
./scripts/stop-index-watch.sh

# 시스템 서비스 (systemd)
# scripts/index-watch.sh를 ExecStart로 등록
```

macOS는 `brew install fswatch`, Linux는 `apt install inotify-tools`가 필요하다.

---

## 배포

### PHP 내장 서버 (개발)

```bash
./run.sh   # 포그라운드
./rund.sh  # 백그라운드
./stop.sh
```

### Apache

`DocumentRoot`를 `public/`으로 지정하고 `FallbackResource /index.php`를 추가한다. `public/.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
```

### Nginx

```nginx
root /path/to/mytory-docs/public;
location / { try_files $uri $uri/ /index.php?$query_string; }
```

---

## 디렉토리 구조

```
public/index.php          # 라우터 + 모든 경로 핸들러
public/assets/            # Tailwind CSS, JS
src/                      # PHP 클래스 (PathParser, FileUtils, Fts5Index 등)
src/views/                # 템플릿 (layout, home, list, view, edit, search)
scripts/                  # CLI (indexer, fswatch wrapper)
tests/                    # PHPUnit
config.php                # 사용자 설정 (gitignored)
CONTEXT.md                # 도메인 용어집
AGENTS.md                 # AI 에이전트 규칙
```

---

## 테스트 & 빌드

```bash
php vendor/bin/phpunit     # 65 tests
npm run build              # Tailwind CSS 컴파일
npm run watch              # 실시간 감시
```

---

## 마이그레이션 (v1 → v2)

| v1 | v2 |
|----|----|
| Bootstrap 3 + jQuery (120KB) | Tailwind CSS v4 (32KB) |
| `<textarea>` | CodeMirror 6 + oneDark |
| 1초 polling 자동저장 | debounced fetch + Ctrl+S |
| ParsedownExtra (2018, unmaintained) | league/commonmark (active) |
| Symfony/Yaml | 정규식 22줄 |
| 검색 없음 | SQLite FTS5 |
| `?path=view:root/file.md` | `/view/root/file.md` |

---

## 라이선스

MIT
