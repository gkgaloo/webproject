// ========================================
// Voting Module - PHP Backend Integration
// ========================================

// API_BASE is defined in auth.js as global var
// var API_BASE = 'backend';

const Voting = {
    // Get all candidates
    async getCandidates(electionId = 0) {
        try {
            const url = electionId > 0
                ? `${API_BASE}/voter/get_candidates.php?election_id=${electionId}`
                : `${API_BASE}/voter/get_candidates.php`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const result = await response.json();
            console.log('API Response (Candidates):', result); // DEBUG
            return result.success ? result.candidates : [];
        } catch (error) {
            console.error('Get candidates error:', error);
            return [];
        }
    },

    // Add new candidate (admin only)
    async addCandidate(candidateData) {
        try {
            const response = await fetch(`${API_BASE}/admin/add_candidate.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify(candidateData)
            });

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Add candidate error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Remove candidate (admin only)
    async removeCandidate(candidateId) {
        try {
            const response = await fetch(`${API_BASE}/admin/delete_candidate.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({ id: candidateId })
            });

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Remove candidate error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Cast vote
    async castVote(candidateId, electionId = 0) {
        try {
            const response = await fetch(`${API_BASE}/voter/cast_vote.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    candidate_id: candidateId,
                    election_id: electionId
                })
            });

            const result = await response.json();

            if (result.success) {
                // Update local user data
                const user = Auth.getCurrentUser();
                if (user) {
                    user.hasVoted = true;
                    localStorage.setItem('current_user', JSON.stringify(user));
                }
            }

            return result;
        } catch (error) {
            console.error('Cast vote error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Check if user has voted
    async hasUserVoted(electionId = 0) {
        try {
            const url = electionId > 0
                ? `${API_BASE}/voter/check_vote_status.php?election_id=${electionId}`
                : `${API_BASE}/voter/check_vote_status.php`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const result = await response.json();
            return result.success ? result.has_voted : false;
        } catch (error) {
            console.error('Check vote status error:', error);
            return false;
        }
    },

    // Get vote results
    async getResults(electionId = 0) {
        try {
            const url = electionId > 0
                ? `${API_BASE}/admin/results.php?election_id=${electionId}`
                : `${API_BASE}/admin/results.php`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success) {
                return result.results || [];
            }
            return [];
        } catch (error) {
            console.error('Get results error:', error);
            return [];
        }
    },

    // Get voting statistics
    async getStats(electionId = 0) {
        try {
            const url = electionId > 0
                ? `${API_BASE}/admin/results.php?election_id=${electionId}`
                : `${API_BASE}/admin/results.php`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success && result.stats) {
                return {
                    totalVoters: result.stats.total_voters,
                    votedCount: result.stats.voted_count,
                    pendingCount: result.stats.pending_count,
                    totalCandidates: result.stats.total_candidates,
                    turnoutPercentage: result.stats.turnout_percentage
                };
            }

            return {
                totalVoters: 0,
                votedCount: 0,
                pendingCount: 0,
                totalCandidates: 0,
                turnoutPercentage: 0
            };
        } catch (error) {
            console.error('Get stats error:', error);
            return {
                totalVoters: 0,
                votedCount: 0,
                pendingCount: 0,
                totalCandidates: 0,
                turnoutPercentage: 0
            };
        }
    },

    // Toggle election status (admin only)
    async toggleElectionStatus(electionId = 1) {
        try {
            // Get current election status
            const response = await fetch(`${API_BASE}/admin/manage_election.php?id=${electionId}`, {
                credentials: 'include'
            });

            const result = await response.json();

            if (!result.success) {
                return { success: false, message: 'Failed to get election status' };
            }

            const currentStatus = result.election.status;
            const newStatus = currentStatus === 'active' ? 'closed' : 'active';

            // Update election status
            const updateResponse = await fetch(`${API_BASE}/admin/manage_election.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    id: electionId,
                    title: result.election.title,
                    description: result.election.description,
                    status: newStatus,
                    start_date: result.election.start_date,
                    end_date: result.election.end_date
                })
            });

            const updateResult = await updateResponse.json();
            return updateResult;
        } catch (error) {
            console.error('Toggle election error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Get election status
    async getElectionStatus(electionId = 0) {
        try {
            const url = electionId > 0
                ? `${API_BASE}/voter/get_election_status.php?id=${electionId}`
                : `${API_BASE}/voter/get_election_status.php`;

            const response = await fetch(url, {
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success && result.election) {
                // Check status strings explicitly
                const status = result.election.status;

                // Also check dates if available in the response? 
                // The backend get_active_election checks dates, so if it returns, it's valid.
                // But for specific ID check, we might want to be careful. 
                // For now, trust the status logic.

                return {
                    isOpen: status === 'active',
                    status: status
                };
            }

            return { isOpen: false, status: 'closed' };
        } catch (error) {
            console.error('Get election status error:', error);
            return { isOpen: false, status: 'closed' };
        }
    },

    // Get all users (admin only)
    async getUsers() {
        try {
            const response = await fetch(`${API_BASE}/admin/get_users.php`, {
                credentials: 'include'
            });

            const result = await response.json();
            return result.success ? result.users : [];
        } catch (error) {
            console.error('Get users error:', error);
            return [];
        }
    }
};
