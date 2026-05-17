const themeColor = document.createElement("meta");
themeColor.name = "theme-color";
document.head.insertBefore(themeColor, document.querySelector("link"));

const availableThemes = ["light", "dark"];

let localTheme = localStorage.getItem("theme");
if (!availableThemes.includes(localTheme)) {
  localTheme = undefined;
}

const appleTheme = (theme) => {
  const isDark = theme
    ? theme === "dark"
    : window.matchMedia("(prefers-color-scheme: dark)").matches;

  if (isDark) {
    document.documentElement.setAttribute("data-theme", "dark");
    themeColor.content = "#050a1a";
  } else {
    document.documentElement.setAttribute("data-theme", "light");
    themeColor.content = "#eef6ff";
  }
};

appleTheme(localTheme);

window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", (e) => {
    if (!availableThemes.includes(localStorage.getItem("theme"))) {
      appleTheme(e.matches ? "dark" : "light");
    }
  });
