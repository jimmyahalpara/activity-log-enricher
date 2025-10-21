# Release Process

This package uses automated releases via GitHub Actions.

## How It Works

When code is pushed to the `main` branch, a GitHub Actions workflow automatically:

1. **Generates a version tag** from the `version` field in `composer.json`
2. **Creates a changelog** from commit messages since the last release
3. **Creates a GitHub release** with the generated notes
4. **Tags the commit** with the version number

## Creating a New Release

### Option 1: Merge to Main (Recommended)

1. Make your changes in the `develop` branch
2. Update the version in `composer.json`:
   ```json
   {
     "version": "1.1.0"
   }
   ```
3. Update `CHANGELOG.md` with your changes
4. Create a pull request to `main`
5. Once merged, the release will be created automatically

### Option 2: Direct Push to Main

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Commit and push to `main`:
   ```bash
   git checkout main
   git add composer.json CHANGELOG.md
   git commit -m "Release v1.1.0"
   git push origin main
   ```

## Version Numbering

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version (1.x.x) - Breaking changes
- **MINOR** version (x.1.x) - New features, backward compatible
- **PATCH** version (x.x.1) - Bug fixes, backward compatible

### Examples

- `1.0.0` → `1.0.1` - Bug fix
- `1.0.1` → `1.1.0` - New feature
- `1.1.0` → `2.0.0` - Breaking change

## Release Notes

The workflow automatically generates release notes from commit messages. To have meaningful release notes:

- Write clear, descriptive commit messages
- Use conventional commit format when possible:
  - `feat: Add Laravel 12 support`
  - `fix: Resolve accessor method name generation`
  - `docs: Update installation instructions`
  - `refactor: Improve code structure`
  - `test: Add tests for nested arrays`

## Manual Release Creation

If you need to create a release manually:

1. Go to GitHub repository
2. Click on "Releases" → "Draft a new release"
3. Create a new tag (e.g., `v1.1.0`)
4. Fill in the release notes
5. Publish the release

## Checking Release Status

After pushing to `main`:

1. Go to your repository on GitHub
2. Click on "Actions" tab
3. Find the "Create Release" workflow
4. Check if it completed successfully

## Troubleshooting

### Release Not Created

- Check that `version` field exists in `composer.json`
- Ensure the tag doesn't already exist
- Verify GitHub Actions have write permissions

### Release Already Exists

The workflow will skip creation if a release with the same version tag already exists. Update the version number in `composer.json` to create a new release.
