<?php
$full_path_for_url = (!empty($parsed['full_path'])) ? convert_from_os_to_utf8($parsed['full_path']) : null;
$full_file_for_url = (!empty($parsed['full_path'])) ? convert_from_os_to_utf8($parsed['full_file']) : null;
?>
<div class="header">
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#main-menu">
                <span class="sr-only">Open Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo BASE_URL ?>" title="Mytory Docs">MD</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="main-menu">
            <ul class="nav navbar-nav">
                <?php if (get_cmd_type() == 'edit' or get_cmd_type() == 'view') { ?>
                    <li><a href="?path=list:<?php echo $full_path_for_url ?>">List</a></li>
                <?php } ?>
                <?php if (get_cmd_type() == 'edit') { ?>
                    <li><a href="?path=view:<?php echo $full_file_for_url ?>">View</a></li>
                <?php } ?>
                <?php if (get_cmd_type() == 'view') { ?>
                    <li><a href="?path=edit:<?php echo $full_file_for_url ?>">Update</a></li>
                <?php } ?>
                <?php if (get_cmd_type() == 'view') { ?>
                    <li>
                        <button class="btn btn-default btn-xs" type="button"
                                onclick="$('body').toggleClass('has-counter')">제목 번호
                        </button>
                    </li>
                <?php } ?>
                <?php if (get_cmd_type() == 'list') { ?>
                    <li>
                        <a href="#" data-toggle="modal" data-target="#new-file"
                           onclick="setTimeout(function(){$('#filename').focus()}, 1000)">
                            New File
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <?php if (get_cmd_type() == 'edit' or get_cmd_type() == 'view') { ?>
                <span class="navbar-text navbar-left">
                    <label for="file_path">File Path</label>
                </span>
                <form class="navbar-form navbar-left">
                    <div class="form-group">
                        <input readonly class="form-control" type="text"
                               value='"<?= convert_from_os_to_utf8($parsed['real_full_file']) ?>"'
                               id="file_path" size="10">
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
                <h4 class="modal-title" id="label-new-file">Enter file name.</h4>
            </div>
            <form role="form" action="<?php echo BASE_URL ?>">
                <div class="modal-body">
                    <input name="path" type="hidden" value="new-file:<?php echo $full_path_for_url ?>"/>

                    <div class="form-group">
                        <label for="filename">File name</label>
                        <input type="text" class="form-control" id="filename" name="filename" placeholder="new file.md">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Modal -->
<div class="modal fade" id="delete-file" tabindex="-1" role="dialog" aria-labelledby="label-delete-file"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="label-delete-file">Really?</h4>
            </div>
            <form role="form" action="<?php echo BASE_URL ?>">
                <div class="modal-body">
                    <p></p>
                    <input name="path" type="hidden" value=""/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!-- /.modal -->
