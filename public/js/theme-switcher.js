window.addEventListener("DOMContentLoaded", () => {
  const themeButtons = [
    document.getElementById("theme-button-automatic"),
    document.getElementById("theme-button-light"),
    document.getElementById("theme-button-dark"),
  ];

  themeButtons.forEach((themeButton) => {
    themeButton.addEventListener("click", () => {
      if (themeButton.dataset.selected) {
        return;
      }

      const theme = themeButton.dataset.theme;

      appleTheme(theme);
      appleThemeButton(theme);

      if (theme) {
        localStorage.setItem("theme", theme);
      } else {
        localStorage.removeItem("theme");
      }
    });
  });

  const appleThemeButton = (theme) => {
    themeButtons.forEach((themeButton) => {
      if (themeButton.dataset.theme === theme) {
        themeButton.setAttribute("data-selected", "true");
      } else if (themeButton.dataset.selected) {
        themeButton.removeAttribute("data-selected");
      }
    });
  };

  appleThemeButton(localTheme);

  const hideOnEsc = {
    name: "hideOnEsc",
    defaultValue: true,
    fn({ hide }) {
      const onKeyDown = (e) => {
        if (e.keyCode === 27) {
          hide();
        }
      };

      return {
        onShow() {
          document.addEventListener("keydown", onKeyDown);
        },
        onHide() {
          document.removeEventListener("keydown", onKeyDown);
        },
      };
    },
  };

  const hideOnPopperBlur = {
    name: "hideOnPopperBlur",
    defaultValue: true,
    fn(instance) {
      return {
        onCreate() {
          instance.popper.addEventListener("focusout", (e) => {
            if (
              instance.props.hideOnPopperBlur &&
              e.relatedTarget &&
              !instance.popper.contains(e.relatedTarget)
            ) {
              instance.hide();
            }
          });
        },
      };
    },
  };

  tippy("#theme-switcher", {
    arrow: false,
    content: document.getElementById("theme-switcher-content"),
    interactive: true,
    offset: [0, 4],
    placement: "bottom-end",
    plugins: [hideOnEsc, hideOnPopperBlur],
    theme: "dropdown",
    trigger: "click",
  });
});
