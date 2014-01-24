<div class="header">
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#main-menu">
                <span class="sr-only">메뉴 열기</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo BASE_URL ?>" title="Mytory Docs">MD</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="main-menu">
            <ul class="nav navbar-nav">
                <?php if(get_cmd_type() == 'edit' OR get_cmd_type() == 'view'){ ?>
                    <li><a href="?path=list:<?php echo $parsed['full_path'] ?>">목록</a></li>
                <?php } ?>
                <?php if(get_cmd_type() == 'view'){ ?>
                    <li><a href="?path=edit:<?php echo $parsed['full_file']?>">수정</a></li>
                <?php } ?>
                <?php if(get_cmd_type() == 'list'){ ?>
                    <li>
                        <a href="#" data-toggle="modal" data-target="#new-file">
                            새 파일
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <?php if(get_cmd_type() == 'edit' OR get_cmd_type() == 'view'){ ?>
                <span class="navbar-text navbar-left">파일경로</span>
                <form class="navbar-form navbar-left">
                    <div class="form-group">
                        <input readonly class="form-control" type="text" value="<?php echo $parsed['real_full_file'] ?>">
                    </div>
                </form>
            <?php } ?>
        </div>
    </nav>
</div>

<!-- Modal -->
<div class="modal fade" id="new-file" tabindex="-1" role="dialog" aria-labelledby="label-new-file" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="label-new-file">파일명을 입력해 주세요.</h4>
            </div>
            <form role="form" action="<?php echo BASE_URL ?>">
                <div class="modal-body">
                    <input name="path" type="hidden" value="new-file:<?php echo $parsed['full_path'] ?>"/>
                    <div class="form-group">
                        <label for="filename">파일명</label>
                        <input type="text" class="form-control" id="filename" name="filename" placeholder="new file.md">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-primary">생성</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal -->
<div class="modal fade" id="delete-file" tabindex="-1" role="dialog" aria-labelledby="label-delete-file" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="label-delete-file">정말로 삭제할까요?</h4>
            </div>
            <form role="form" action="<?php echo BASE_URL ?>">
                <div class="modal-body">
                    <p></p>
                    <input name="path" type="hidden" value=""/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">삭제</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->