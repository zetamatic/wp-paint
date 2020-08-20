
/**
 * @namespace wp
 */
window.wp = window.wp || {};

(function( exports, $ ) {
  wp.media.wpp_modals_queue = [];
  wp.media.view.Modal.prototype._open = wp.media.view.Modal.prototype.open;
  wp.media.wpp_modal_close_refresh_url = false;
  wp.media.view.Modal = wp.media.view.Modal.extend({
    open: function () {
      if(wp.media.wpp_modals_queue.length > 3) {
        wp.media.wpp_modals_queue.shift();
      }
      wp.media.wpp_modals_queue.push(this);
      return wp.media.view.Modal.prototype._open.apply( this, arguments );
    }
  });
  /// Fix for WordPress 5.2.3
  wp.media.view.Attachment.Details.prototype._attributes = wp.media.view.Attachment.Details.prototype.attributes;
  wp.media.view.Attachment.Details.prototype.attributes = function () {
    return {
      'tabIndex': 0,
      'data-id': this.model.get( 'id' )
    };
  };

  wp.media.wpp_refresh_frame = function () {
    if (wp.media.frame.content.get()!==null && wp.media.frame.content.get().collection && wp.media.frame.content.get().collection.props){
      wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
      wp.media.frame.content.get().options.selection.reset();
    } else {
      wp.media.frame.library.props.set({ignore: (+ new Date())});
    }
  };

  window.onload = function() {
    window.addEventListener("beforeunload", function (e) {
      if($('#wpp-editor-frame:visible').length < 1)
        return undefined;
      var editor_window = $('#wpp-editor-frame:visible')[0].contentWindow;
      var confirmationMessage = "You have unsaved changes! \n\nYour changes will be lost if you close this item without saving.";

      if(editor_window.MP_Base_state && editor_window.MP_Base_state.layers_archive) {
        var la = editor_window.MP_Base_state.layers_archive;
        if(la.length < 1 || null == la[0]) {
          return undefined;
        } else {
          (e || window.event).returnValue = confirmationMessage; //Gecko + IE
          return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        }
      }
    });
  };

  $.initialize(".media-modal-close", function () {
    var $modal_close_btn = $(this);
    $modal_close_btn.on('click', function () {
      if($('#wpp-editor-frame:visible').length < 1)
        return true;
      var editor_window = $('#wpp-editor-frame:visible')[0].contentWindow;
      if(editor_window.MP_Base_state && editor_window.MP_Base_state.layers_archive) {
        var la = editor_window.MP_Base_state.layers_archive;
        if(la.length < 1 || null == la[0]) {
          return true;
        } else {
          return confirm("You have unsaved changes! \n\nYour changes will be lost if you close this item without saving.");
        }
      }
      return true;
    });
  });

  $.initialize(".edit-attachment", function () {
    var $edit_attachment_button = $(this);
    var $attachment_details = $edit_attachment_button.parents('.attachment-details');
    var attachment_id = $attachment_details.data('id');
    var $wpp_edit_attachment_button = $attachment_details.parent().find('.wpp-edit-attachment');
    if(!attachment_id || $attachment_details.length < 1 || $edit_attachment_button.length < 1 || $wpp_edit_attachment_button.length > 0)
      return;
    var $wpp_edit_attachment_button = jQuery('<a href="javascript:void(0);" class="wpp-edit-attachment" data-attachment-id="'+attachment_id+'">Edit Image using WP Paint</a>');
    $edit_attachment_button.after($wpp_edit_attachment_button);
    if($edit_attachment_button.hasClass('button')) {
      $wpp_edit_attachment_button.addClass('button');
    }
    $wpp_edit_attachment_button.click(function () {
      var $self = $(this);
      var attachment_id = $self.data('attachmentId');
      var btn_is_in_modal = $self.parents().find('.media-frame').length > 0;
      if(window.location.search.match('item=') && window.location.href.match('upload.php')) {
        wp.media.wpp_modal_close_refresh_url = window.location.href;
      } else {
        wp.media.wpp_modal_close_refresh_url = false;
      }
      window.wpp_open_editor(attachment_id, function (fr) {
        var wpp_modals_queue_length = wp.media.wpp_modals_queue.length;
        if(wpp_modals_queue_length > 1 && btn_is_in_modal) {
          var last_frm = wp.media.wpp_modals_queue[wpp_modals_queue_length - 2];
          last_frm.controller.close();
        }
      }, function (fr) {
        var wpp_modals_queue_length = wp.media.wpp_modals_queue.length;
        if(wpp_modals_queue_length > 1 && btn_is_in_modal) {
          var last_frm = wp.media.wpp_modals_queue[wpp_modals_queue_length - 2];
          last_frm.controller.open();
          wp.media.wpp_refresh_frame();
        }
        if(wp.media.wpp_modal_close_refresh_url && wp.media.wpp_modal_close_refresh_url.match(/^https?:\/\//g)) {
          window.location.href = wp.media.wpp_modal_close_refresh_url;
        }
      });
    });
  });
  window.wpp_open_editor = function (attachment_id, open_callback, close_callback) {
    var open_callback = (typeof open_callback == "undefined") ? null : open_callback;
    var close_callback = (typeof close_callback == "undefined") ? null : close_callback;
    var wpp_edit_attachment_frame = wp.media.frames.wpp_edit_attachment_frame = wp.media({
      button : {},
      title: 'WP Paint',
      toolbar: null
    });
    wpp_edit_attachment_frame.on('open', function () {
      var $el = wpp_edit_attachment_frame.$el;
      var $attachment_frame = $el.parent().parent();
      $attachment_frame.addClass('wp-paint-modal');
      $attachment_frame.find('.media-frame-title>h1').text('WP Paint');
      $attachment_frame.find('.edit-media-header').hide();
      $attachment_frame.find('.media-frame-router').remove();
      $attachment_frame.find('.media-frame-toolbar').remove();
      $attachment_frame.find('.media-frame-content').css({
        'overflow': 'hidden',
        'top': '50px',
        'bottom': '0px'
      });
      $attachment_frame.find('.media-frame-content').html('<iframe src="'+wp_paint_admin_meta.wp_admin_url+'admin.php?page=wp-paint-edit-image&id='+attachment_id+'&_='+Math.random()+'" id="wpp-editor-frame" frameborder="0" style="width: 100%; height: 100%; border: none;"></iframe>');
      if(open_callback)
        open_callback(this);
    });
    wpp_edit_attachment_frame.on('close', function () {
      var $el = wpp_edit_attachment_frame.$el;
      var $attachment_frame = $el.parent().parent();
      $attachment_frame.find('.media-frame-content').html('<center><strong></strong></center>')
      if($('#post_type').length > 0 && $('#post_type').val() == "attachment") {
        window.location.reload();
      }
      if(close_callback)
        close_callback(this);
    });
    wpp_edit_attachment_frame.open();
  }
  var wpp_setup_attachment_edit_button = function () {
    if($('#post_type').length < 1 || $('#post_ID').length < 1 || $('#post_type').val() != "attachment")
      return;
    var attachment_id = $('#post_ID').val();
    var $edit_attachment_button = $('.wp_attachment_image input[type="button"][id^="imgedit-open-btn-"]');
    var $wpp_edit_attachment_button = $('<button type="button" class="button wpp-edit-attachment" data-attachment-id="'+attachment_id+'">Edit Image Using WP-Paint</button>');
    $wpp_edit_attachment_button.click(function () {
      var $self = $(this);
      var attachment_id = $self.data('attachmentId');
      window.wpp_open_editor(attachment_id);
    });
    $edit_attachment_button.after($wpp_edit_attachment_button);
  };
  $(function () {
    wpp_setup_attachment_edit_button();
  });
})( wp, jQuery );
