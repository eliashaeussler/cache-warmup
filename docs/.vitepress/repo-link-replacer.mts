// @ts-ignore
import path from 'path';

export class RepoLinkReplacer
{
    constructor(
        private readonly repoUrl: string,
        private readonly rootPath: string,
    ) {}

    public replaceLink(link: string, templatePath: string): string
    {
        if (!link.startsWith('../') || link.includes('#')) {
            return link;
        }

        const fullTemplatePath = path.resolve(this.rootPath, templatePath);
        const fullTargetPath = path.resolve(path.dirname(fullTemplatePath), link);
        const relativePath = path.relative(this.rootPath, fullTargetPath);

        if (!relativePath.startsWith('../')) {
            return link;
        }

        const blobUrl = `${this.repoUrl}/blob/main/${relativePath.substring(3)}`;

        if (process.env.NODE_ENV !== 'production') {
            console.log(`Replaced link: ${link} → ${blobUrl}`);
        }

        return blobUrl;
    }
}
