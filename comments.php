<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
function threadedComments($comments, $options)
{
    // 初始化评论类名为空字符串
    $commentClass = '';
    // 判断评论作者ID是否存在，并且是否与文章所有者ID相同
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author'; // 为作者添加特定类名
        } else {
            $commentClass .= ' comment-by-user'; // 为普通用户添加特定类名
        }
    }

    // 根据评论层级添加不同的CSS类
    $commentLevelClass = $comments->levels > 0 ? ' comment-child' : ' comment-parent';
    $depth = $comments->levels + 1;

    // 如果评论者提供了URL，则创建一个链接到其网站的名字；否则仅显示名字
    if ($comments->url) {
        $author = '<a href="' . $comments->url . '" target="_blank" rel="external nofollow">' . $comments->author . '</a>';
    } else {
        $author = $comments->author;
    } ?>

    <li id="li-<?php $comments->theId(); ?>" class="comment-body<?php
                                                                if ($depth > 1 && $depth < 3) {
                                                                    echo ' comment-child ';
                                                                    $comments->levelsAlt('comment-level-odd', ' comment-level-even');
                                                                } else if ($depth > 2) {
                                                                    echo ' comment-child2';
                                                                    $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
                                                                } else {
                                                                    echo ' comment-parent';
                                                                }
                                                                $comments->alt(' comment-odd', ' comment-even');
                                                                ?>">
        <div id="<?php $comments->theId(); ?>">
            <?php
            $size = '80';
            $default = 'mm';
            $rating = Helper::options()->commentsAvatarRating;
            $email = $comments->mail;
            $avatar = \Typecho\Common::gravatarUrl($email, $size, $rating, $default);
            ?>
            <div class="comment-view" onclick="">
                <div class="comment-header">
                    <img class="avatar" src="<?php echo $avatar ?>" width="<?php echo $size ?>" height="<?php echo $size ?>" />
                    <span class="comment-author<?php echo $commentClass; ?>"><?php echo $author; ?></span>
                </div>
                <div class="comment-content">
                    <span class="comment-author-at"><?php echo getCommentAt($comments->coid); ?></span> <?php $comments->content(); ?></p>
                </div>
                <div class="comment-meta">
                    <time class="comment-time"><?php $comments->date(); ?></time>
                    <span class="comment-reply" data-no-instant><?php $comments->reply('Reply'); ?></span>
                </div>
            </div>
        </div>
        <?php if ($comments->children) { ?>
            <div class="comment-children">
                <?php $comments->threadedComments($options); ?>
            </div>
        <?php } ?>
    </li>
<?php } ?>
<script src="<?php $this->options->themeUrl('js/myQuery.js'); ?>"></script>

<div id="<?php $this->respondId(); ?>" class="comment-container">
    <div id="comments" class="clearfix">
        <?php $this->comments()->to($comments); ?>
        <?php if ($this->allow('comment')): ?>
            <div id="respond">
                <span class="response"><?php _e('Responses'); ?>
                <?php if ($this->user->hasLogin()): ?> / You are <a href="<?php $this->options->profileUrl(); ?>" data-no-instant><?php $this->user->screenName(); ?></a> here, do you want to <a href="<?php $this->options->logoutUrl(); ?>" title="Logout" data-no-instant>logout</a> ?
                    <?php endif; ?> <?php $comments->cancelReply(' / Cancel Reply'); ?></span>
                <form method="post" action="<?php echo $this->permalink; ?>comment" id="comment-form" class="comment-form" role="form" onsubmit="getElementById('misubmit').disabled=true;return true;">
                    <?php if (!$this->user->hasLogin()): ?>
                        <input type="text" name="author" maxlength="12" id="author" class="form-control input-control clearfix" placeholder="Name (*)" value="" required>
                        <input type="email" name="mail" id="mail" class="form-control input-control clearfix" placeholder="Email (*)" value="" <?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                        <input type="url" name="url" id="url" class="form-control input-control clearfix" placeholder="Site (https://)" value="" <?php if ($this->options->commentsRequireURL): ?> required<?php endif; ?>>
                    <?php endif; ?>
                    <textarea name="text" id="textarea" class="form-control" placeholder="Your comment here. Be cool. " required><?php $this->remember('text', false); ?></textarea>
                    <button type="submit" class="submit" id="misubmit">SUBMIT</button>
                    <?php $security = $this->widget('Widget_Security'); ?>
                    <input type="hidden" name="_" value="<?php echo $security->getToken($this->request->getReferer()) ?>">
                </form>
            </div>
        <?php else : ?>
            <span class="response">The article has been posted for too long and comments have been automatically closed.</span>
        <?php endif; ?>

        <?php if ($comments->have()): ?>

            <?php $comments->listComments(); ?>

            <div class="lists-navigator clearfix">
                <?php $comments->pageNav('←', '→', '2', '...'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<script>
    var TypechoComment = {
        reply: function(cid, coid) {
            // 新位置插入
            $('#' + cid).after($('#respond'));
            $('#respond').hide().fadeIn();
            // 把post地址更新一下
            let post_action = "<?php echo $this->permalink; ?>comment?parent=" + coid;
            $("#comment-form").attr("action", post_action)
            return false
        }
    };
    // (function() {
    //     window.TypechoComment = {
    //         dom: function(id) {
    //             return document.getElementById(id);
    //         },
    //         create: function(tag, attr) {
    //             var el = document.createElement(tag);
    //             for (var key in attr) {
    //                 el.setAttribute(key, attr[key]);
    //             }
    //             return el;
    //         },
    //         reply: function(cid, coid) {
    //             var comment = this.dom(cid),
    //                 parent = comment.parentNode,
    //                 response = this.dom('<?php echo $this->respondId(); ?>'),
    //                 input = this.dom('comment-parent'),
    //                 form = 'form' == response.tagName ? response : response.getElementsByTagName('form')[0],
    //                 textarea = response.getElementsByTagName('textarea')[0];
    //             if (null == input) {
    //                 input = this.create('input', {
    //                     'type': 'hidden',
    //                     'name': 'parent',
    //                     'id': 'comment-parent'
    //                 });

    //                 form.appendChild(input);
    //             }
    //             input.setAttribute('value', coid);
    //             if (null == this.dom('comment-form-place-holder')) {
    //                 var holder = this.create('div', {
    //                     'id': 'comment-form-place-holder'
    //                 });

    //                 response.parentNode.insertBefore(holder, response);
    //             }
    //             comment.appendChild(response);
    //             this.dom('cancel-comment-reply-link').style.display = '';
    //             if (null != textarea && 'text' == textarea.name) {
    //                 textarea.focus();
    //             }
    //             return false;
    //         },
    //         cancelReply: function() {
    //             var response = this.dom('<?php echo $this->respondId(); ?>'),
    //                 holder = this.dom('comment-form-place-holder'),
    //                 input = this.dom('comment-parent');
    //             if (null != input) {
    //                 input.parentNode.removeChild(input);
    //             }
    //             if (null == holder) {
    //                 return true;
    //             }
    //             this.dom('cancel-comment-reply-link').style.display = 'none';
    //             holder.parentNode.insertBefore(response, holder);
    //             return false;
    //         }
    //     };
    // })();
    <?php if (!$this->user->hasLogin()): ?>

        // function getCommentCookie(name) {
        //     var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        //     if (arr = document.cookie.match(reg)) {
        //         return unescape(decodeURI(arr[2]));
        //     } else {
        //         return null;
        //     }
        // }

        // function addCommentInputValue() {
        //     document.getElementById('author').value = getCommentCookie('<?php echo md5($this->request->getUrlPrefix()); ?>__typecho_remember_author');
        //     document.getElementById('mail').value = getCommentCookie('<?php echo md5($this->request->getUrlPrefix()); ?>__typecho_remember_mail');
        //     document.getElementById('url').value = getCommentCookie('<?php echo md5($this->request->getUrlPrefix()); ?>__typecho_remember_url');
        // }
        // addCommentInputValue();
    <?php endif; ?>
</script>