<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
// 自定义评论函数，递归生成
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
<link href="<?php $this->options->themeUrl('css/noQueryEmoji.min.css'); ?>" rel="stylesheet">
<script src="<?php $this->options->themeUrl('js/noQueryEmoji.min.js'); ?>"></script>
<script src="<?php $this->options->themeUrl('js/emoji.list.js'); ?>"></script>

<div id="<?php $this->respondId(); ?>" class="comment-container">
    <div id="comments" class="clearfix">
        <?php $this->comments()->to($comments); ?>
        <?php if ($this->allow('comment')): ?>
            <div id="respond">
                <span class="response"><?php echo __('Responses'); ?>
                    <?php if ($this->user->hasLogin()):
                        echo ' | ' . __('User Account') . ':'; ?>
                        <a href="<?php $this->options->profileUrl(); ?>" data-no-instant><?php $this->user->screenName(); ?></a> | <a href="<?php $this->options->logoutUrl(); ?>" title="<?php echo __('Click to Logout'); ?>" data-no-instant><?php echo __('Click to Logout'); ?></a>
                    <?php endif; ?>
                    <a href="javascript:void(0);" rel="nofollow" style="float:right;display:none;" id="cancelReply" onclick="return TypechoComment.cancelReply();"><?php echo __('Cancel Reply'); ?></a>
                </span>
                <form method="post" action="<?php echo $this->permalink; ?>comment" id="comment-form" class="comment-form" role="form" onsubmit="getElementById('misubmit').disabled=true;return true;">
                    <?php if (!$this->user->hasLogin()): ?>
                        <input type="text" name="author" maxlength="12" id="author" class="form-control input-control clearfix" placeholder="Name (*)" value="" required>
                        <input type="email" name="mail" id="mail" class="form-control input-control clearfix" placeholder="Email (*)" value="" <?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                        <input type="url" name="url" id="url" class="form-control input-control clearfix" placeholder="Site (https://)" value="" <?php if ($this->options->commentsRequireURL): ?> required<?php endif; ?>>
                    <?php endif; ?>
                    <textarea name="text" id="textarea" class="form-control" placeholder="<?php echo __('Typing isn\'t tiring, keep smiling'); ?>" required><?php $this->remember('text', false); ?></textarea>
                    <div id="emoji_button" class="emoji_button"></div>
                    <button type="submit" class="submit" id="misubmit"><?php echo __('submit'); ?></button>
                    <?php $security = $this->widget('Widget_Security'); ?>
                    <input type="hidden" name="_" value="<?php echo $security->getToken($this->request->getReferer()) ?>">
                </form>
            </div>
        <?php else : ?>
            <span class="response"><?php echo __('Comments on this article are closed'); ?></span>
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
    var getCookie = function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return undefined;
    };

    var TypechoComment = {
        reply: function(cid, coid) {
            // 新位置插入
            $('#' + cid).after($('#respond'));
            $('#respond').hide().fadeIn();
            // 把post地址更新一下
            let post_action = "<?php echo $this->permalink; ?>comment?parent=" + coid;
            $("#comment-form").attr("action", post_action)
            $("#cancelReply").show();
            return false
        },
        cancelReply: function() {
            // 取消回复的隐藏和显示
            $("#cancelReply").hide();
            $("#comments").prepend($('#respond'));
            $('body').scrollTo('#comments', 300, 0);
            return false
        },
        isEmail: function(email) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailRegex.test(email);
        },
        isUrl: function(url) {
            const urlRegex = /^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/[^ ]*)?$/;
            return urlRegex.test(url);
        },
        // 对于评论过的朋友通过前端（比对cookie）自动补充昵称等信息的初始化
        load: function() {
            // 对于登录的用户不需要填写
            const IS_LOGIN = <?php echo $this->user->hasLogin() ? 'true' : 'false'; ?>;
            if (IS_LOGIN) return;
            // cookie的前缀
            const COOKIE_PREFIX = '<?php echo md5($this->request->getUrlPrefix()); ?>__typecho_remember';
            // 从 Cookie 中读取用户信息
            let author = getCookie(`${COOKIE_PREFIX}_author`),
                mail = getCookie(`${COOKIE_PREFIX}_mail`),
                url = getCookie(`${COOKIE_PREFIX}_url`);

            // 如果存在信息，则填充到表单中
            if (author !== undefined) $('#author').val(decodeURIComponent(author));
            if (mail !== undefined && this.isEmail(decodeURIComponent(mail))) $('#mail').val(decodeURIComponent(mail));
            if (url !== undefined && this.isUrl(decodeURIComponent(url))) $('#url').val(decodeURIComponent(url));
        },
        // 初始化表情
        initEmoji: function() {
            $("#textarea").emoji({
                button: "#emoji_button",
                showTab: true,
                animation: 'fade',
                basePath: '<?php $this->options->themeUrl('images/emoji'); ?>',
                icons: emojiLists
            });
        },
        // 解析评论的表情
        parseEmoji: function() {
            $(".comment-list li").emojiParse({
                basePath: '<?php $this->options->themeUrl('images/emoji'); ?>',
                icons: emojiLists // 注：详见 js/emoji.list.js
            });
        },
    };
    (function() {
        TypechoComment.load();
        TypechoComment.initEmoji();
        TypechoComment.parseEmoji();
    })();
</script>