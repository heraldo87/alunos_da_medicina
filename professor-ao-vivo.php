<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

$pageTitle = 'Professor ao Vivo | ' . $brand['name'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle); ?></title>

    <meta name="description" content="Professor de medicina em tempo real por voz.">

    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/branding.css')); ?>">
</head>
<body class="realtime-focus-body">

<main class="realtime-focus-page">

    <header class="realtime-focus-header">
        <a href="/dashboard.php" class="brand realtime-focus-brand">
            <span class="brand-logo">
                <?= brand_logo_svg(28); ?>
            </span>

            <span>
                <span class="brand-name"><?= e($brand['name']); ?></span>
                <small>Professor de Medicina ao Vivo</small>
            </span>
        </a>

        <div class="realtime-focus-header-actions">
            <span class="badge badge-success">Beta</span>
            <a href="/dashboard.php" class="btn btn-outline btn-sm">Voltar ao dashboard</a>
        </div>
    </header>

    <section class="realtime-focus-layout">

        <aside class="realtime-instructions-panel">
            <div class="realtime-panel-section">
                <span class="badge">Orientações</span>

                <h2>Como conversar melhor</h2>

                <ul class="realtime-instruction-list">
                    <li>
                        <strong>Fale uma pergunta por vez.</strong>
                        <span>Isso ajuda o professor a responder com mais clareza.</span>
                    </li>

                    <li>
                        <strong>Peça para simplificar.</strong>
                        <span>Exemplo: “Explique como se eu estivesse no primeiro ano.”</span>
                    </li>

                    <li>
                        <strong>Use temas acadêmicos.</strong>
                        <span>Anatomia, fisiologia, bioquímica, histologia e revisão conceitual.</span>
                    </li>

                    <li>
                        <strong>Evite dados de pacientes.</strong>
                        <span>Não informe nomes, casos reais identificáveis ou dados sensíveis.</span>
                    </li>
                </ul>
            </div>

            <div class="realtime-panel-section realtime-educational-warning">
                <strong>Uso educacional.</strong>
                <p>
                    Esta ferramenta não substitui professores, livros-texto, preceptores,
                    avaliação profissional, diagnóstico ou conduta médica.
                </p>
            </div>

            <div class="realtime-panel-section">
                <h3>Exemplos de perguntas</h3>

                <button class="realtime-example" type="button">
                    Explique potencial de membrana de forma simples.
                </button>

                <button class="realtime-example" type="button">
                    Me ajude a revisar circulação pulmonar e sistêmica.
                </button>

                <button class="realtime-example" type="button">
                    Qual a diferença entre mitose e meiose?
                </button>
            </div>
        </aside>

        <section class="realtime-main-stage">

            <div class="realtime-stage-card">
                <div class="realtime-stage-top">
                    <span class="badge">Tempo real por voz</span>

                    <div id="realtimeStatus" class="realtime-status-pill">
                        Desconectado
                    </div>
                </div>

                <div class="realtime-professor-avatar-area">
                    <div class="realtime-orb-large" id="realtimeOrb">
                        <span></span>
                    </div>

                    <h1>Professor de Medicina</h1>

                    <p>
                        Clique em iniciar, permita o microfone e converse naturalmente.
                    </p>
                </div>

                <div class="voice-visualizer-card">
                    <div class="voice-visualizer-header">
                        <strong>Gráfico da voz</strong>
                        <span id="voiceLabel">Aguardando microfone</span>
                    </div>

                    <canvas id="voiceCanvas" width="900" height="180"></canvas>
                </div>

                <div class="realtime-actions realtime-actions-centered">
                    <button id="startRealtime" class="btn btn-primary" type="button">
                        Iniciar conversa
                    </button>

                    <button id="stopRealtime" class="btn btn-outline" type="button" disabled>
                        Encerrar conversa
                    </button>
                </div>
            </div>

        </section>

    </section>

</main>

<script>
let pc = null;
let dc = null;
let localStream = null;
let remoteAudio = null;

let localSessionId = null;
let auditNonce = null;

let audioContext = null;
let analyser = null;
let sourceNode = null;
let animationFrameId = null;

const startButton = document.querySelector('#startRealtime');
const stopButton = document.querySelector('#stopRealtime');
const statusBox = document.querySelector('#realtimeStatus');
const orb = document.querySelector('#realtimeOrb');
const voiceCanvas = document.querySelector('#voiceCanvas');
const voiceLabel = document.querySelector('#voiceLabel');
const canvasContext = voiceCanvas.getContext('2d');

function setStatus(message, connected = false) {
    statusBox.textContent = message;
    statusBox.classList.toggle('connected', connected);
    orb.classList.toggle('active', connected);
}

function resizeCanvasForDisplay() {
    const rect = voiceCanvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    voiceCanvas.width = Math.max(1, Math.floor(rect.width * dpr));
    voiceCanvas.height = Math.max(1, Math.floor(rect.height * dpr));

    canvasContext.setTransform(dpr, 0, 0, dpr, 0, 0);
}

function clearVoiceCanvas() {
    const width = voiceCanvas.clientWidth;
    const height = voiceCanvas.clientHeight;

    canvasContext.clearRect(0, 0, width, height);

    canvasContext.globalAlpha = 0.28;
    canvasContext.lineWidth = 1;

    for (let i = 1; i < 4; i++) {
        const y = (height / 4) * i;
        canvasContext.beginPath();
        canvasContext.moveTo(0, y);
        canvasContext.lineTo(width, y);
        canvasContext.stroke();
    }

    canvasContext.globalAlpha = 1;
}

function drawVoiceVisualizer() {
    if (!analyser) {
        clearVoiceCanvas();
        return;
    }

    const width = voiceCanvas.clientWidth;
    const height = voiceCanvas.clientHeight;

    const bufferLength = analyser.frequencyBinCount;
    const dataArray = new Uint8Array(bufferLength);

    analyser.getByteFrequencyData(dataArray);

    canvasContext.clearRect(0, 0, width, height);

    const bars = 64;
    const step = Math.floor(bufferLength / bars);
    const barGap = 4;
    const barWidth = Math.max(3, (width - (bars * barGap)) / bars);

    for (let i = 0; i < bars; i++) {
        const value = dataArray[i * step] || 0;
        const percent = value / 255;
        const barHeight = Math.max(6, percent * height * 0.82);

        const x = i * (barWidth + barGap);
        const y = (height - barHeight) / 2;

        canvasContext.beginPath();
        canvasContext.roundRect(x, y, barWidth, barHeight, 8);
        canvasContext.fill();
    }

    animationFrameId = requestAnimationFrame(drawVoiceVisualizer);
}

async function startVoiceVisualizer(stream) {
    resizeCanvasForDisplay();

    audioContext = new AudioContext();
    analyser = audioContext.createAnalyser();
    analyser.fftSize = 256;
    analyser.smoothingTimeConstant = 0.78;

    sourceNode = audioContext.createMediaStreamSource(stream);
    sourceNode.connect(analyser);

    voiceLabel.textContent = 'Microfone ativo';
    drawVoiceVisualizer();
}

function stopVoiceVisualizer() {
    if (animationFrameId) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }

    if (sourceNode) {
        sourceNode.disconnect();
        sourceNode = null;
    }

    if (audioContext) {
        audioContext.close();
        audioContext = null;
    }

    analyser = null;
    voiceLabel.textContent = 'Aguardando microfone';

    clearVoiceCanvas();
}


function sendAuditEvent(eventType, role = 'system', text = '', metadata = {}) {
    if (!localSessionId || !auditNonce) {
        return;
    }

    const payload = {
        local_session_id: localSessionId,
        audit_nonce: auditNonce,
        event_type: eventType,
        role,
        text,
        metadata
    };

    const json = JSON.stringify(payload);

    if (eventType === 'session_ended' && navigator.sendBeacon) {
        const blob = new Blob([json], { type: 'application/json' });
        navigator.sendBeacon('/realtime/audit.php', blob);
        return;
    }

    fetch('/realtime/audit.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: json,
        keepalive: true
    }).catch((error) => {
        console.warn('Falha ao enviar auditoria:', error);
    });
}

function handleRealtimeAuditEvent(data) {
    if (!data || typeof data !== 'object') {
        return;
    }

    if (data.type === 'conversation.item.input_audio_transcription.completed') {
        const transcript = data.transcript || data.text || '';

        if (transcript.trim() !== '') {
            sendAuditEvent('input_audio_transcription_completed', 'user', transcript, {
                realtime_type: data.type
            });
        }
    }

    if (data.type === 'response.audio_transcript.done') {
        const transcript = data.transcript || data.text || '';

        if (transcript.trim() !== '') {
            sendAuditEvent('response_audio_transcript_done', 'assistant', transcript, {
                realtime_type: data.type
            });
        }
    }

    if (data.type === 'error') {
        sendAuditEvent('realtime_error', 'system', data.error?.message || 'Erro Realtime', {
            realtime_type: data.type
        });
    }
}

function stopRealtime() {
    sendAuditEvent('session_ended', 'system', 'Sessão Realtime encerrada.');
    if (dc) {
        dc.close();
        dc = null;
    }

    if (pc) {
        pc.close();
        pc = null;
    }

    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }

    if (remoteAudio) {
        remoteAudio.pause();
        remoteAudio.srcObject = null;
        remoteAudio.remove();
        remoteAudio = null;
    }

    stopVoiceVisualizer();

    startButton.disabled = false;
    stopButton.disabled = true;

    setStatus('Desconectado', false);

    localSessionId = null;
    auditNonce = null;
}

async function startRealtime() {
    try {
        startButton.disabled = true;
        stopButton.disabled = false;

        setStatus('Criando sessão segura...');

        const tokenResponse = await fetch('/realtime/session.php', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        });

        const tokenData = await tokenResponse.json();

        if (!tokenResponse.ok) {
            throw new Error(tokenData.error || 'Não foi possível criar sessão.');
        }

        localSessionId = tokenData.local_session_id || null;
        auditNonce = tokenData.audit_nonce || null;

        const ephemeralKey =
            tokenData.value ||
            tokenData.client_secret?.value ||
            tokenData.secret?.value ||
            null;

        if (!ephemeralKey) {
            console.error(tokenData);
            throw new Error('A sessão não retornou uma credencial temporária.');
        }

        setStatus('Solicitando microfone...');

        localStream = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        });

        await startVoiceVisualizer(localStream);

        pc = new RTCPeerConnection();

        remoteAudio = document.createElement('audio');
        remoteAudio.autoplay = true;
        remoteAudio.style.display = 'none';
        document.body.appendChild(remoteAudio);

        pc.ontrack = (event) => {
            remoteAudio.srcObject = event.streams[0];
        };

        localStream.getTracks().forEach(track => {
            pc.addTrack(track, localStream);
        });

        dc = pc.createDataChannel('oai-events');

        dc.onopen = () => {
            setStatus('Conectado. Pode falar.', true);

            sendAuditEvent('session_started', 'system', 'Sessão Realtime iniciada.');

            dc.send(JSON.stringify({
                type: 'response.create',
                response: {
                    instructions: 'Cumprimente brevemente o aluno e pergunte qual tema de medicina ele quer revisar agora.'
                }
            }));
        };

        dc.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);

                handleRealtimeAuditEvent(data);

                if (data.type === 'error') {
                    console.error('Erro Realtime:', data);
                    setStatus('Erro na conversa', false);
                }

                if (data.type === 'response.done') {
                    setStatus('Conectado. Pode continuar.', true);
                }
            } catch (error) {
                console.log('Evento Realtime:', event.data);
            }
        };

        pc.onconnectionstatechange = () => {
            if (!pc) {
                return;
            }

            if (pc.connectionState === 'connected') {
                setStatus('Conectado. Pode falar.', true);
            }

            if (['failed', 'disconnected', 'closed'].includes(pc.connectionState)) {
                setStatus('Conexão encerrada ou instável.', false);
            }
        };

        setStatus('Conectando ao professor...');

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        const sdpResponse = await fetch('https://api.openai.com/v1/realtime/calls', {
            method: 'POST',
            body: offer.sdp,
            headers: {
                'Authorization': 'Bearer ' + ephemeralKey,
                'Content-Type': 'application/sdp'
            }
        });

        if (!sdpResponse.ok) {
            const errorText = await sdpResponse.text();
            throw new Error('Erro SDP: ' + errorText);
        }

        const answer = {
            type: 'answer',
            sdp: await sdpResponse.text()
        };

        await pc.setRemoteDescription(answer);

    } catch (error) {
        console.error(error);
        setStatus(error.message || 'Erro ao iniciar conversa.', false);
        stopRealtime();
    }
}

document.querySelectorAll('.realtime-example').forEach((button) => {
    button.addEventListener('click', () => {
        alert('Sugestão de fala:\\n\\n' + button.textContent.trim());
    });
});

startButton.addEventListener('click', startRealtime);
stopButton.addEventListener('click', stopRealtime);

window.addEventListener('resize', () => {
    resizeCanvasForDisplay();
    clearVoiceCanvas();
});

window.addEventListener('beforeunload', stopRealtime);

resizeCanvasForDisplay();
clearVoiceCanvas();
</script>

</body>
</html>
