# Mytory Docs

파일 시스템 기반 개인용 마크다운 문서 브라우저/에디터. 데이터베이스 없이 디렉토리 구조 자체를 콘텐츠 저장소로 사용.

## 언어

**doc_root** (문서 뿌리):
파일 시스템의 절대경로. 하나의 콘텐츠 컬렉션 단위. `config.php`에서 이름-경로 쌍으로 정의.
예: `'글' => '/Users/mytory/Dropbox/Documents/글'`
_피할 것_: folder root, base directory, source root, document collection

**root_name** (뿌리 이름):
doc_root의 별칭. URL path 파라미터의 첫 세그먼트.
예: `list:글/IT/`에서 `글`이 root_name.
_피할 것_: root alias, doc_root label, collection name

**relative_path** (상대경로):
doc_root 내부의 상대 디렉토리 경로. 파일명은 포함하지 않음.
예: `view:글/IT/react.md`에서 `IT`가 relative_path.
_피할 것_: sub-path, inner directory path

**cmd** (명령):
전체 path의 첫 세그먼트. 화면 타입 또는 동작을 결정.
값: `view`, `edit`, `list`, `new-file`, `delete-file`
_피할 것_: action, mode, route, command type

**path** (경로):
`{cmd}:{root_name}/{relative_path}` 또는 `{cmd}:{root_name}/{relative_path}/{file}` 형식의 완전한 문서 경로 식별자. URL의 `?path=` 파라미터 값.
_피할 것_: route, URI, document URI

**YAML front matter** (머리말):
마크다운 파일 상단의 `---`로 둘러싸인 메타데이터 블록. `title`, `date`, `tags` 등을 포함. Jekyll 호환.
_피할 것_: header, preamble, metadata block

**OS_ENCODING** (OS 인코딩):
운영체제가 파일 시스템에 사용하는 인코딩. Linux/macOS는 UTF-8, 한국 Windows는 CP949. `auto_config.php`에서 자동 감지.
_피할 것_: file encoding, system charset

**BACKUP_PATH** (백업 경로):
파일 저장 시 자동 생성되는 timestamped 백업이 저장되는 디렉토리.
기본값: `./backup/{root_name}/{relative_path}/{timestamp}__{file}`
_피할 것_: backup directory, snapshot folder
