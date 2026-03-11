import fs from 'fs';
import path from 'path';

const emojiRegex = /(\p{Emoji_Presentation}|\p{Emoji}\uFE0F)/gu;

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.resolve(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else if (file.endsWith('.vue') || file.endsWith('.ts')) {
            results.push(file);
        }
    });
    return results;
}

const files = walk('c:/Users/creci.com/Herd/bomsamaritano/resources/js');
const output = [];
files.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    const lines = content.split('\n');
    lines.forEach((line, i) => {
        if (emojiRegex.test(line)) {
            output.push(`${file}:${i + 1}: ${line.trim()}`);
        }
    });
});
fs.writeFileSync('emojis.txt', output.join('\n'), 'utf8');
