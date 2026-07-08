# AGENTS.md

Mytory Docs 개발 시 AI 에이전트가 지켜야 할 규칙입니다.

## 코드 변경 후 검증

1. **서버 재시작**: 코드 변경 후 반드시 PHP 내장 서버를 재시작한다.
   ```bash
   kill $(lsof -ti:1111) 2>/dev/null; sleep 1
   php -S localhost:1111 -t public public/index.php > /tmp/mytory-server.log 2>&1 &
   ```

2. **브라우저 검증**: 서버 재시작 후 `agent-browser`로 변경된 URL을 실제로 열어 확인한다.
   ```bash
   agent-browser open "http://localhost:1111/{변경된_경로}"
   agent-browser snapshot -i
   ```

3. **PHP 에러 로그 확인**: 변경 후 로그에 PHP Fatal/Parse/Uncaught 에러가 없는지 확인한다.
   ```bash
   grep "PHP Fatal\|PHP Parse\|Uncaught" /tmp/mytory-server.log
   ```

4. **테스트 실행**:
   ```bash
   php vendor/bin/phpunit
   ```

## URL 검증 체크리스트

변경한 기능의 모든 URL을 agent-browser로 열어봐야 한다:

| 기능 | 검증할 URL |
|------|-----------|
| 홈 | `http://localhost:1111/` |
| 리스트 | `http://localhost:1111/list/{root_name}` |
| 뷰 | `http://localhost:1111/view/{root_name}/{path}` |
| 에디터 | `http://localhost:1111/edit/{root_name}/{path}` |
| 검색 | `http://localhost:1111/search?q={query}` |
| 이미지 | `http://localhost:1111/image/{root_name}/{path}` |

## CSS 빌드

HTML 클래스 변경 시 Tailwind를 리빌드해야 한다:
```bash
npx @tailwindcss/cli -i public/assets/app.css -o public/assets/build.css --minify
```

## 주의할 점

- `public/index.php`가 라우터 스크립트이므로 정적 파일은 `return false`로 패스스루해야 한다.
- `config.php`의 doc_roots는 사용자 환경마다 다르므로, 모든 doc_root가 존재하지 않을 수 있다.
- 한국어 파일명/디렉토리명은 `rawurlencode`/`rawurldecode`로 정확히 처리해야 한다.
