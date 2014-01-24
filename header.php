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