(function($) {
  showSuccessToast = function() {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Успешно',
      text: 'Задача успешно выполнена.',
      showHideTransition: 'slide',
      icon: 'success',
      loaderBg: '#f96868',
      position: 'top-right'
    })
  };
  showInfoToast = function() {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Info',
      text: 'And these were just the basic demos! Scroll down to check further details on how to customize the output.',
      showHideTransition: 'slide',
      icon: 'info',
      loaderBg: '#46c35f',
      position: 'top-right'
    })
  };
  showWarningToast = function() {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Warning',
      text: 'And these were just the basic demos! Scroll down to check further details on how to customize the output.',
      showHideTransition: 'slide',
      icon: 'warning',
      loaderBg: '#57c7d4',
      position: 'top-right'
    })
  };
  showDangerToast = function() {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Ошибка!',
      text: 'Ошибка при выполнении задания! Пожалуйста, попробуйте еще раз.',
      showHideTransition: 'slide',
      icon: 'error',
      loaderBg: '#f2a654',
      position: 'top-right'
    })
  };
  showToastPosition = function(position) {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Positioning',
      text: 'Specify the custom position object or use one of the predefined ones',
      position: String(position),
      icon: 'success',
      stack: false,
      loaderBg: '#f96868'
    })
  }
  showSuccessMessageInTopCenter = function(position) {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Muvofaqqiyatli xabar',
      text: 'Sizning so`rovinggiz muvofaqqiyatli bajarildi',
      position: String(position),
      icon: 'success',
      stack: false,
      loaderBg: '#f96868'
    })
  }
  showErrorMessageInTopCenter = function(position) {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Xatolik',
      text: 'Xatolik mavjud! Iltimos qaytadan urinib ko`ring',
      position: String(position),
      icon: 'error',
      stack: false,
      loaderBg: '#f96868'
    })
  }
  showToastInCustomPosition = function() {
    'use strict';
    resetToastPosition();
    $.toast({
      heading: 'Custom positioning',
      text: 'Specify the custom position object or use one of the predefined ones',
      icon: 'success',
      position: {
        left: 120,
        top: 120
      },
      stack: false,
      loaderBg: '#f96868'
    })
  }
  resetToastPosition = function() {
    $('.jq-toast-wrap').removeClass('bottom-left bottom-right top-left top-right mid-center'); // to remove previous position class
    $(".jq-toast-wrap").css({
      "top": "",
      "left": "",
      "bottom": "",
      "right": ""
    }); //to remove previous position style
  }
})(jQuery);
