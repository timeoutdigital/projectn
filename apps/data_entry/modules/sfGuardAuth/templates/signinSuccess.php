<?php use_helper('I18N') ?>

<div id="sf_admin_container">

    <?php if ($sf_user->hasFlash('error')): ?>
      <div class="error"><?php echo $sf_user->getFlash('error') ?></div>
    <?php endif ?>

    <form action="<?php echo url_for('@sf_guard_signin') ?>" method="post">
      <table>
        <?php echo $form ?>
      </table>

      <input type="submit" value="<?php echo __('sign in') ?>" />
      <a href="<?php echo url_for('@sf_guard_password') ?>"><?php echo __('Forgot your password?') ?></a>
    </form>

</div>