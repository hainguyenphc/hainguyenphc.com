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
      let isStickyState = false;
      // Callback executed once the image is clicked.
      buttonView.set({
        label: 'Full screen',
        icon: icon,
        tooltip: true
      });
      buttonView.on('execute', () => {
        if (state === 1) {
          editor.sourceElement.nextElementSibling.removeAttribute('data-fullscreen');
          document.body.removeAttribute('data-fullscreen');
          buttonView.set({
            label: 'Full screen',
            icon: icon,
            tooltip: true
          });
          state = 0;
          editor.sourceElement.nextElementSibling.scrollIntoView({block: 'center'});
          editor.focus();
          editor.ui.view.stickyPanel.isSticky = isStickyState;
        } else {
          editor.sourceElement.nextElementSibling.setAttribute('data-fullscreen', 'fullscreeneditor');
          document.body.setAttribute('data-fullscreen', 'fullscreenoverlay');
          buttonView.set({
            label: 'Mode Normal',
            icon: iconCancel,
            tooltip: true
          });
          state = 1;
          isStickyState = editor.ui.view.stickyPanel.isSticky;
          editor.ui.view.stickyPanel.isSticky = !isStickyState;
        }
      });
      return buttonView;
    });
  }
}
