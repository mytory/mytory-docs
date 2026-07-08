# AGENTS.md

AI 에이전트가 이 프로젝트를 다룰 때 지켜야 할 규칙.

## 철학

이 도구는 **개인용**이다. 내가 오래 쓸 수 있는, 단순하고 실용적인 선택을 우선한다.

- 복잡한 건 집어넣지 않는다. 기능 하나 넣을 때마다 "이거 없으면 진짜 불편한가?"를 묻는다.
- 한국어 문서를 1급 시민으로 대접한다. 영어권 도구의 기본값을 그대로 가져오지 않는다.
- 라이브러리는 꼭 필요할 때만. 정규식 20줄로 되는 일에 Composer 패키지를 추가하지 않는다.
- **변경한 건 브라우저로 확인한다.** 콘솔 로그만 보고 "아마 될 거예요" 하지 않는다.

## 코드 변경 후 반드시 할 것

```bash
# 1. 서버 재시작
kill $(lsof -ti:1111) 2>/dev/null; sleep 1
php -S localhost:1111 -t public public/index.php > /tmp/mytory-server.log 2>&1 &

# 2. 모든 관련 URL을 agent-browser로 열어서 확인
agent-browser open "http://localhost:1111/변경된_경로"
agent-browser snapshot -i

# 3. 에러 로그 확인
grep "PHP Fatal\|PHP Parse\|Uncaught" /tmp/mytory-server.log

# 4. 테스트
php vendor/bin/phpunit
```

## 검증 URL 체크리스트

변경한 기능과 관련된 모든 URL을 브라우저로 열어봐야 한다:

| 기능 | URL |
|------|-----|
| 홈 | `http://localhost:1111/` |
| 리스트 | `http://localhost:1111/list/{root}` |
| 뷰 (한글 경로) | `http://localhost:1111/view/{root}/{path}` |
| 에디터 | `http://localhost:1111/edit/{root}/{path}` |
| 검색 | `http://localhost:1111/search?q={query}` |
| 이미지 프록시 | `http://localhost:1111/image/{root}/{path}` |
| JSON API | `http://localhost:1111/api/search?q={query}` |

에디터는 특히 **텍스트가 실제로 로드되는지** 확인해야 한다. CodeMirror가 ES 모듈 CDN에서 로드되기 때문에 import 실패 시 빈 화면이 된다.

## CSS 빌드

HTML 클래스를 변경했으면 Tailwind를 다시 빌드한다:

```bash
npx @tailwindcss/cli -i public/assets/app.css -o public/assets/build.css --minify
```

## 주의할 점

- `public/index.php`가 라우터 스크립트다. PHP 내장 서버에서 정적 파일(`.css`, `.js`)을 제대로 서빙하려면 `return false`를 반환해야 한다.
- `config.php`의 doc_roots는 환경마다 다르고, 없는 디렉토리도 있다. 접근 불가능한 root는 조용히 건너뛴다.
- 한국어 파일명/디렉토리명은 `rawurlencode()`/`rawurldecode()`를 써야 한다. `urlencode()`는 공백을 `+`로 바꾸므로 파일명이 깨진다.
- `~`는 한국어에서 범위 표시("10월~12월")로 흔히 쓰인다. ~~strikethrough~~ 기능은 v2에서 의도적으로 제거했다.
- 문서 제목 번호는 자동 감지한다: h2에 이미 번호가 있으면 끄고, 없으면 켠다. 사용자가 수동으로 토글한 값이 localStorage에 저장되면 그게 최우선이다.
