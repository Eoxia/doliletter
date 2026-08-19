import re
path = 'core/tpl/spread/public_spread_view.tpl.php'
with open(path, 'r', encoding='windows-1252') as f:
    content = f.read()

target = r"            const \$respObj = \$\('\<div/\>'\)\.html\(resp\)\.find\('input'\); const message = \$respObj\.val\(\);\n            const isError = \$respObj\.attr\('id'\) === 'error';"

replacement = """            let message = "Erreur inconnue";
            let isError = true;
            if (typeof resp === "string") {
                const matchSuccess = resp.match(/id="success"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="success"/i);
                const matchError = resp.match(/id="error"[^>]*value="([^"]+)"/i) || resp.match(/value="([^"]+)"[^>]*id="error"/i);
                if (matchSuccess) {
                    message = matchSuccess[1];
                    isError = false;
                } else if (matchError) {
                    message = matchError[1];
                } else {
                    message = resp.substring(0, 100);
                }
            } else {
                message = "Type non géré: " + (typeof resp);
            }"""

content = re.sub(target, replacement, content)

with open(path, 'w', encoding='windows-1252') as f:
    f.write(content)
print("Done")
