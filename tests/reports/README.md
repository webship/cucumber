# Cucumber reports

Output from the webship-js suite. `cucumber.js` writes two files here:

- `cucumber_report.json`: machine-readable JSON used by the HTML generator
  and by CI tooling (JUnit, GitLab artefacts).
- `cucumber_report.html`: the HTML report rendered by webship-js after
  each run.

Generated files are gitignored. The folder and this README are tracked so
the layout survives a fresh clone.
