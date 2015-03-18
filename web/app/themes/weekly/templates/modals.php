<div class="modal fade add-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add content</h4>
      </div>
      <div class="modal-body">
        <div id="error" class="alert alert-danger" role="alert">
          <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
          <span class="sr-only">Error:</span>
          Please make sure all fields have populated.
        </div>
        <div class="entry-form standard">
          <form method="post" action="" class="data_form">
            <input type="hidden" name="ajax-nonce" id="ajax-nonce" value="<?= wp_create_nonce( 'submit-nonce' ); ?>" />
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" required>
            </div>
            <div class="form-group">
              <label for="title">Description <br /><small>To embed oEmbed content (YouTube, Vimeo, Twitter, etc) just paste the URL on a seperate paragraph.</small></label>
              <textarea id="summernote" name="content" rows="18" required>
          </textarea>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default modal-close" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary modal-submit">Submit</button>
      </div>
    </div>
  </div>
</div>
