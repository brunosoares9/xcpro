<?php
/**
 * XC PRO PHP - Dynamic installation frontend
 */
$title = "XC PRO";
$logo = "https://i.pinimg.com/474x/55/e6/b4/55e6b4246fa6fe1ac7846b94ee7da798.jpg";
$description = "Cliente XC PRO para Stremio com suporte a F4M Proxy";
$version = "1.0.0";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Instalação de Addons XC PRO</title>
    <link rel="shortcut icon" href="<?php echo $logo; ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b0c10;
            color: #eaeaea;
        }
        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative selection:bg-purple-600 selection:text-white overflow-x-hidden">
    
    <!-- Decorative Blurs -->
    <div class="absolute top-10 left-10 w-80 h-80 bg-purple-950/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-indigo-950/25 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header bar -->
    <header class="border-b border-gray-800/60 bg-[#0d0f14]/80 backdrop-blur-md relative z-10">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2.5 bg-gradient-to-tr from-purple-700 to-indigo-600 rounded-xl shadow-lg shadow-purple-900/20">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h1 class="font-display font-bold text-2xl tracking-tight bg-gradient-to-r from-purple-400 via-pink-400 to-indigo-400 bg-clip-text text-transparent">
                XC PRO
              </h1>
              <p class="text-[10px] uppercase tracking-widest text-[#8a5aab] font-bold">Cliente Xtream Codes para stremio</p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-xs bg-gray-800/80 text-gray-400 px-3 py-1 rounded-full border border-gray-800 font-mono">v<?php echo $version; ?></span>
            <span class="text-xs bg-green-500/10 text-green-400 px-3 py-1 rounded-full border border-green-500/10 font-semibold flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span> Servidor Pronto
            </span>
          </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-grow max-w-6xl mx-auto w-full px-4 py-8 relative z-10 flex flex-col lg:flex-row gap-8">
        
        <!-- Form Section -->
        <section class="flex-1 space-y-6">
          <div class="bg-[#0e1218] border border-gray-800/70 rounded-2xl p-6 shadow-xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-600 via-pink-500 to-indigo-600"></div>
            
            <div class="mb-5">
              <h2 class="text-lg font-display font-semibold text-white flex items-center gap-2">
                ✍️ Configuração do Provedor
              </h2>
              <p class="text-xs text-gray-400 mt-1">
                Construa sua URL personalizada criptografada diretamente no seu navegador. Os dados nunca saem da sua sessão do navegador.
              </p>
            </div>

            <div class="space-y-4">
              
              <!-- Host Input -->
              <div>
                <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                  <span>Endereço Host do Servidor *</span>
                  <span class="text-[10px] text-gray-500 lowercase font-mono">http://meudns.com:8080</span>
                </label>
                <input
                  type="url"
                  id="hInput"
                  placeholder="http://site-do-painel.com:port"
                  class="w-full bg-[#131921] border border-gray-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl py-2.5 px-4 text-sm text-white placeholder-gray-500 outline-none transition-all duration-200"
                />
              </div>

              <!-- Username & Password Row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- username -->
                <div>
                  <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                    Nome de Usuário *
                  </label>
                  <input
                    type="text"
                    id="uInput"
                    placeholder="Seu login"
                    class="w-full bg-[#131921] border border-gray-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl py-2.5 px-4 text-sm text-white placeholder-gray-500 outline-none transition-all duration-200"
                  />
                </div>

                <!-- password -->
                <div>
                  <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                    Senha de Acesso *
                  </label>
                  <input
                    type="password"
                    id="pInput"
                    placeholder="Sua senha secreta"
                    class="w-full bg-[#131921] border border-gray-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl py-2.5 px-4 text-sm text-white placeholder-gray-500 outline-none transition-all duration-200"
                  />
                </div>

              </div>

              <!-- F4M Proxy optional field -->
              <div class="pt-3 border-t border-gray-800/40 mt-4">
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-xs font-semibold text-purple-300 uppercase tracking-wider">
                    F4M Proxy (Opcional)
                  </label>
                  <button
                    id="tutorialBtn"
                    type="button"
                    class="text-xs bg-gray-800 hover:bg-purple-900/40 text-purple-400 hover:text-purple-300 px-3 py-1.5 rounded-lg transition flex items-center gap-2 border border-purple-500/30 hover:border-purple-500/60"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    📖 Tutorial F4M Proxy
                  </button>
                </div>
                <input
                  type="url"
                  id="prInput"
                  placeholder="http://192.168.0.4:9090"
                  class="w-full bg-[#131921] border border-gray-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-xl py-2.5 px-4 text-sm text-white placeholder-gray-500 outline-none transition-all duration-200"
                />
                <p class="text-[11px] text-gray-400 mt-2 leading-relaxed bg-purple-950/10 p-3 rounded-lg border border-purple-950/20">
                  ⚠️ Caso configurado, as Streams geradas pelo addon passarão de forma remapeada pelo seu Proxy Local F4M, permitindo compatibilidade imediata com certos players de TS/M3U8.
                </p>
              </div>

            </div>

          </div>

          <!-- Documentation / Hosting info -->
          <!-- <div class="bg-[#0e1218]/60 border border-gray-800/40 rounded-2xl p-5">
            <h3 class="text-sm font-display font-medium text-white mb-2">Sobre este Servidor PHP</h3>
            <p class="text-xs text-gray-400 leading-relaxed">
              Este pacote é totalmente autônomo. Você pode pegar a pasta deste addon e carregar no seu servidor web (como Hostgator, Locaweb, VPS, Apache, cPanel ou Nginx) com suporte PHP e ele funcionará imediatamente graças às regras configuradas no seu arquivo <code class="text-purple-400 font-mono font-bold">.htaccess</code>.
            </p>
          </div> -->

        </section>

        <!-- Sidebar Install Center Column -->
        <section class="w-full lg:w-[400px] shrink-0 space-y-6">
          <div class="bg-[#0e1218] border border-gray-800/70 rounded-2xl p-6 shadow-xl flex flex-col justify-between min-h-[365px] border-purple-950">
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-display font-bold text-white uppercase tracking-wider">Centro de Instalação</h3>
                <span class="text-[10px] bg-purple-500/10 text-purple-300 px-2 py-0.5 border border-purple-500/20 rounded font-medium">Auto-Gerador</span>
              </div>

              <!-- Unconfigured -->
              <div id="noConfig" class="flex flex-col items-center justify-center py-12 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3 text-purple-400 opacity-40 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <p class="text-xs font-semibold text-gray-400">Dados incompletos</p>
                <p class="text-[11px] px-3 mt-1.5 leading-relaxed">
                  Insira o host, usuário e senha para começar a gerar seus links de sincronização de forma imediata.
                </p>
              </div>

              <!-- Configured Link Output -->
              <div id="configActive" class="space-y-5 hidden">
                <p class="text-xs text-gray-300 leading-relaxed">
                  Seus links personalizados foram compilados localmente com sucesso!
                </p>

                <div class="space-y-3 pt-2">
                  <a
                    id="deepLink"
                    href="#"
                    class="w-full bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition duration-150 transform active:scale-95"
                  >
                    INSTALAR NO STREMIO
                  </a>
                  
                  <a
                    id="webLink"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="w-full bg-gray-800 hover:bg-gray-700 text-white border border-gray-700 font-semibold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition duration-150"
                  >
                    Instalar no Stremio Web
                  </a>
                </div>

                <div class="pt-4 border-t border-gray-800/50 space-y-2">
                  <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    Link Manual HTTP do Manifesto
                  </label>
                  <div class="flex gap-2">
                    <input
                      type="text"
                      id="urlField"
                      readonly
                      class="flex-grow bg-[#131921] border border-gray-800 rounded-lg px-3 py-2 text-xs font-mono text-purple-300 outline-none select-all"
                    />
                    <button
                      id="copyBtn"
                      type="button"
                      class="bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 px-3 rounded-lg transition shrink-0 text-xs font-medium"
                    >
                      Copiar
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-800/40 mt-6 flex justify-between items-center text-[10px] text-gray-500">
              <span>🔒 Privado & Seguro</span>
              <span>XC PRO PHP</span>
            </div>
          </div>
        </section>

      </main>

      <footer class="bg-[#080a0d] border-t border-gray-950 py-6 text-center text-xs text-gray-500 relative z-10">
        <p>© 2026 XC PRO PHP System. Todos os direitos reservados.</p>
      </footer>

    </div>

    <script type="text/javascript">
        const hInput = document.getElementById('hInput');
        const uInput = document.getElementById('uInput');
        const pInput = document.getElementById('pInput');
        const prInput = document.getElementById('prInput');
        const tutorialBtn = document.getElementById('tutorialBtn');
        
        const noConfig = document.getElementById('noConfig');
        const configActive = document.getElementById('configActive');
        const deepLink = document.getElementById('deepLink');
        const webLink = document.getElementById('webLink');
        const urlField = document.getElementById('urlField');
        const copyBtn = document.getElementById('copyBtn');

        // Botão do tutorial
        tutorialBtn.addEventListener('click', () => {
            window.open('https://github.com/zoreu/f4mproxy', '_blank');
        });

        function generate() {
            const host = hInput.value.trim();
            const username = uInput.value.trim();
            const password = pInput.value.trim();
            const f4mProxy = prInput.value.trim();

            if (!host || !username || !password) {
                noConfig.classList.remove('hidden');
                configActive.classList.add('hidden');
                return;
            }

            noConfig.classList.add('hidden');
            configActive.classList.remove('hidden');

            const configObj = {
                host: host,
                username: username,
                password: password,
                ...(f4mProxy ? { f4mProxy: f4mProxy } : {})
            };

            const jsonStr = JSON.stringify(configObj);
            const b64 = btoa(unescape(encodeURIComponent(jsonStr)))
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=/g, '');

            const origin = window.location.protocol + "//" + window.location.host;
            let path = window.location.pathname;
            if (path.endsWith('index.php')) {
                path = path.replace('index.php', '');
            }
            if (!path.endsWith('/')) {
                path += '/';
            }

            const manifestUrl = origin + path + "b64/" + b64 + "/manifest.json";
            const deep = "stremio://" + window.location.host + path + "b64/" + b64 + "/manifest.json";

            urlField.value = manifestUrl;
            deepLink.href = deep;
            webLink.href = "https://web.stremio.com/#/addons?addon=" + encodeURIComponent(manifestUrl);
        }

        [hInput, uInput, pInput, prInput].forEach(inp => {
            inp.addEventListener('input', generate);
        });

        copyBtn.addEventListener('click', () => {
            urlField.select();
            document.execCommand('copy');
            const originalVal = copyBtn.innerText;
            copyBtn.innerText = 'Copiado!';
            setTimeout(() => {
                copyBtn.innerText = originalVal;
            }, 1800);
        });
    </script>
</body>
</html>