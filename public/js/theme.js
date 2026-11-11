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

  document.documentElement.setAttribute(
    "data-theme",
    isDark ? "dark" : "light",
  );

  themeColor.content = getComputedStyle(document.documentElement)
    .getPropertyValue("--surface-page-start")
    .trim();
};

appleTheme(localTheme);

window
  .matchMedia("(prefers-color-scheme: dark)")
  .addEventListener("change", (e) => {
    if (!availableThemes.includes(localStorage.getItem("theme"))) {
      appleTheme(e.matches ? "dark" : "light");
    }
  });
