# Releasing

This project uses semantic versioning and annotated Git tags. Stable release tags
use the version without a `v` prefix (for example, `2.7.3`). The package version
in `composer.json` and the latest release heading in `changelog.md` must match.

## Choosing the version

- Patch (`2.7.x`): backwards-compatible bug fixes, data updates, or an additional
  supported bank/BIC.
- Minor (`2.x.0`): backwards-compatible features.
- Major (`x.0.0`): breaking changes.

## Release checklist

1. Start from the intended release branch and ensure the working tree is clean.
2. Update `composer.json`'s `version` field.
3. Add a release section at the top of the relevant major-version section in
   `changelog.md`.
4. Run the local checks:

   ```bash
   make lint
   make test_unit
   git diff --check
   ```

   Run integration and acceptance tests when `.env` credentials are available:

   ```bash
   make test_integration
   make test_acceptance
   ```

5. Review the diff, then create the release commit:

   ```bash
   git diff --stat
   git diff
   git add composer.json changelog.md src tests
   git commit -m "Release X.Y.Z: short summary"
   ```

6. Create and publish the matching annotated tag:

   ```bash
   git tag -a X.Y.Z -m "Release X.Y.Z"
   git push origin <release-branch>
   git push origin X.Y.Z
   ```

7. Create the GitHub release from tag `X.Y.Z` and verify that Composer exposes
   the new version. Do not reuse a version or move an existing release tag.

If the release is prepared on a feature branch, merge that branch into the
project's release branch before pushing the tag. Never include credentials or
`.env` files in a release commit.
