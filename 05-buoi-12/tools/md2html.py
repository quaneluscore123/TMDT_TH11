"""Chuyen van ban .md (tieu de, doan, danh sach, bang, **dam**, [lien ket](url)) sang HTML de dang len trang WordPress.
Dung: python md2html.py van-ban/*.md -o out/   (bo dong tieu de '# ...' vi WordPress da co tieu de trang)"""
import html, re, sys, pathlib

def inline(s):
    s = html.escape(s, quote=False)
    s = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', s)
    s = re.sub(r'~~(.+?)~~', r'<del>\1</del>', s)
    s = re.sub(r'`(.+?)`', r'<code>\1</code>', s)
    return re.sub(r'\[(.+?)\]\((.+?)\)', r'<a href="\2">\1</a>', s)

def convert(text):
    out, lines, i = [], text.splitlines(), 0
    while i < len(lines):
        ln = lines[i].rstrip()
        if not ln.strip() or ln.startswith('# '):
            i += 1; continue
        m = re.match(r'(#{2,4}) (.*)', ln)
        if m:
            n = len(m.group(1)); out.append(f'<h{n}>{inline(m.group(2))}</h{n}>'); i += 1; continue
        if ln.startswith('|'):
            rows = []
            while i < len(lines) and lines[i].startswith('|'):
                rows.append([c.strip() for c in lines[i].strip().strip('|').split('|')]); i += 1
            head, body = rows[0], [r for r in rows[2:]]
            t = '<table><thead><tr>' + ''.join(f'<th>{inline(c)}</th>' for c in head) + '</tr></thead><tbody>'
            t += ''.join('<tr>' + ''.join(f'<td>{inline(c)}</td>' for c in r) + '</tr>' for r in body)
            out.append(t + '</tbody></table>'); continue
        if re.match(r'\s*(- |\d+\. )', ln):
            tag = 'ol' if re.match(r'\s*\d+\. ', ln) else 'ul'
            items = []
            while i < len(lines) and re.match(r'\s*(- |\d+\. )', lines[i]):
                cur = re.sub(r'^\s*(- |\d+\. )', '', lines[i]); sub = []; i += 1
                while i < len(lines) and re.match(r'\s{2,}- ', lines[i]):
                    sub.append(inline(lines[i].strip()[2:])); i += 1
                items.append(inline(cur) + ('<ul>' + ''.join(f'<li>{s}</li>' for s in sub) + '</ul>' if sub else ''))
            out.append(f'<{tag}>' + ''.join(f'<li>{x}</li>' for x in items) + f'</{tag}>'); continue
        if ln.startswith('> '):
            out.append(f'<blockquote><p>{inline(ln[2:])}</p></blockquote>'); i += 1; continue
        para = []
        while i < len(lines) and lines[i].strip() and not re.match(r'(#|\||- |\d+\. |> )', lines[i]):
            para.append(inline(lines[i].strip())); i += 1
        out.append('<p>' + '<br>'.join(para) + '</p>')
    return '\n'.join(out)

if __name__ == '__main__':
    args = sys.argv[1:]; dst = pathlib.Path(args[args.index('-o') + 1]); dst.mkdir(parents=True, exist_ok=True)
    for f in args[:args.index('-o')]:
        p = pathlib.Path(f); (dst / (p.stem + '.html')).write_text(convert(p.read_text(encoding='utf-8')), encoding='utf-8')
        print('->', dst / (p.stem + '.html'))
