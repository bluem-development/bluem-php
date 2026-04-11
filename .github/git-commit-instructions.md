Use semantic versioning for commit messages. Follow the format:

```<type>(<scope>): <subject>```

Include if the change is breaking or not, and if it is a fix or a feature. For example:
- `feat(auth): add JWT support`
- `fix(api): resolve CORS issue`
- `chore(deps): update dependencies`


A breaking change should be indicated with an exclamation mark after the type, like `feat!: change API endpoint`. This helps maintain a clear history and allows for automated versioning and changelog generation. 
Example: 
- `feat!: change API endpoint` (indicates a breaking change)
- `fix!: update authentication flow` (indicates a breaking change)

