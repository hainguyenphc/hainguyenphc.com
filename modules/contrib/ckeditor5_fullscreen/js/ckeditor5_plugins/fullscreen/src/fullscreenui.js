/**
 * @file registers the fullscreen toolbar button and binds functionality to it.
 */

import {
  Plugin
} from 'ckeditor5/src/core';
import {
  ButtonView
} from 'ckeditor5/src/ui';
import icon from '../../../../icons/fullscreen-big.svg';
import iconCancel from '../../../../icons/fullscreen-cancel.svg';

export default class FullscreenUI extends Plugin {

  init() {
    const editor = this.editor;

    // This will register the fullscreen toolbar button.
    editor.ui.componentFactory.add('fullscreen', locale => {
      const buttonView = new ButtonView(locale);
      let state = 0;
      // Callback executed once the image is clicked.
      buttonView.set({
        label: 'Full screen',
        icon: icon,
        tooltip: true
      });
      buttonView.on('execute', () => {
        if (state == 1) {
          editor.sourceElement.nextElementSibling.removeAttribute('id');
          document.body.removeAttribute('id');
          buttonView.set({
            label: 'Full screen',
            icon: icon,
            tooltip: true
          });
          state = 0;
        } else {
          editor.sourceElement.nextElementSibling.setAttribute("id", 'fullscreeneditor');
          document.body.setAttribute("id", "fullscreenoverlay");
          buttonView.set({
            label: 'Mode Normal',
            icon: iconCancel,
            tooltip: true
          });
          state = 1;
        }
      });
      return buttonView;
    });
  }
}
