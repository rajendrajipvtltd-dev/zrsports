<script>
    // API Calls के लिए एक helper function
    async function fetchFromAPI(endpoint) {
        const proxyUrl = `/api-proxy.php?url=${endpoint}`;
        try {
            const response = await fetch(proxyUrl);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error("API Fetch Error:", error);
            return null;
        }
    }

    // 🏏 Live Scores Load करने का Function
    async function loadLiveScores() {
        const grid = document.getElementById('matchesGrid');
        grid.innerHTML = '<div style="text-align:center;">⏳ Loading live scores...</div>';
        
        // 1. Live Scores fetch करें
        const liveData = await fetchFromAPI('livescores/inplay?include=participants;scores');
        
        if (liveData && liveData.data && liveData.data.length > 0) {
            // 2. Data को UI में render करें
            const matchesHtml = liveData.data.map(match => {
                // participants array se team names निकालें
                const teamNames = match.participants?.map(p => p.name).join(' vs ') || 'Match';
                // scores array se score निकालें
                const matchScores = match.scores?.filter(s => s.description === 'CURRENT').map(s => `${s.score.goals}`).join(' - ') || '';
                
                return `
                    <div class="match-card">
                        <span class="status-badge">${match.state?.name?.toUpperCase() || 'LIVE'}</span>
                        <h3>${teamNames}</h3>
                        <p>🏆 ${match.league?.name || 'Match'}</p>
                        <p>📍 ${match.venue?.name || 'Venue TBA'}</p>
                        <p class="team-score">Score: ${matchScores}</p>
                    </div>
                `;
            }).join('');
            grid.innerHTML = matchesHtml;
        } else {
            // Agar कोई live match नहीं है तो upcoming या featured match दिखाएँ
            await loadUpcomingMatches(); // Fallback function
        }
    }

    // 📅 Upcoming / Featured Match Load करने का Function (Fallback)
    async function loadUpcomingMatches() {
        const grid = document.getElementById('matchesGrid');
        // Yahan aap kisi specific fixture ka ID daal sakte hain, jaise aapne share kiya: 19427199
        const fixtureData = await fetchFromAPI('fixtures/19427199?include=participants;league;venue;state;scores');
        
        if (fixtureData && fixtureData.data) {
            const match = fixtureData.data;
            const teamNames = match.participants?.map(p => p.name).join(' vs ') || 'Match';
            const matchTime = new Date(match.starting_at).toLocaleString();
            const matchStatus = match.state?.name || 'UPCOMING';
            
            const matchHtml = `
                <div class="match-card">
                    <span class="status-badge">${matchStatus}</span>
                    <h3>${teamNames}</h3>
                    <p>🏆 ${match.league?.name || 'Match'}</p>
                    <p>📍 ${match.venue?.name || 'Venue TBA'}</p>
                    <p>⏱️ Starts at: ${matchTime}</p>
                </div>
            `;
            grid.innerHTML = matchHtml;
            
            // Extra info add करें
            const note = document.createElement('div');
            note.style.cssText = 'text-align:center; margin-top:20px; font-size:0.85rem; color:#aaa; grid-column:1/-1;';
            note.innerText = '📡 No live matches at the moment. Showing featured upcoming match.';
            grid.appendChild(note);
        } else {
            grid.innerHTML = '<div style="text-align:center;">No matches found.</div>';
        }
    }

    // 🚀 Page Load होते ही Live Scores Load करें
    loadLiveScores();
    // Har 30 seconds में update करें
    setInterval(loadLiveScores, 30000);
</script>
