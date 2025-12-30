// ========================================
// Storage Module - localStorage Management
// ========================================

const Storage = {
    // Storage keys
    keys: {
        USERS: 'voting_users',
        CANDIDATES: 'voting_candidates',
        VOTES: 'voting_votes',
        CURRENT_USER: 'voting_current_user',
        ELECTION_STATUS: 'voting_election_status'
    },

    // Initialize storage with default data
    init() {
        if (!this.get(this.keys.USERS)) {
            this.set(this.keys.USERS, [
                {
                    id: 'admin',
                    name: 'Administrator',
                    email: 'admin@voting.com',
                    password: 'admin123',
                    role: 'admin'
                }
            ]);
        }

        if (!this.get(this.keys.CANDIDATES)) {
            this.set(this.keys.CANDIDATES, [
                {
                    id: 'c1',
                    name: 'Sarah Johnson',
                    party: 'Progressive Alliance',
                    description: 'Advocating for education reform and sustainable development.',
                    image: '👩‍💼'
                },
                {
                    id: 'c2',
                    name: 'Michael Chen',
                    party: 'Tech Innovation Party',
                    description: 'Focusing on digital infrastructure and innovation.',
                    image: '👨‍💻'
                },
                {
                    id: 'c3',
                    name: 'Emily Rodriguez',
                    party: 'Green Future Coalition',
                    description: 'Champion of environmental protection and renewable energy.',
                    image: '👩‍🌾'
                },
                {
                    id: 'c4',
                    name: 'David Thompson',
                    party: 'Economic Growth Party',
                    description: 'Promoting business development and job creation.',
                    image: '👨‍💼'
                }
            ]);
        }

        if (!this.get(this.keys.VOTES)) {
            this.set(this.keys.VOTES, {});
        }

        if (!this.get(this.keys.ELECTION_STATUS)) {
            this.set(this.keys.ELECTION_STATUS, {
                isOpen: true,
                startDate: new Date().toISOString(),
                endDate: null
            });
        }
    },

    // Get data from localStorage
    get(key) {
        try {
            const data = localStorage.getItem(key);
            return data ? JSON.parse(data) : null;
        } catch (error) {
            console.error('Error reading from storage:', error);
            return null;
        }
    },

    // Set data to localStorage
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error('Error writing to storage:', error);
            return false;
        }
    },

    // Remove data from localStorage
    remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Error removing from storage:', error);
            return false;
        }
    },

    // Clear all voting data (admin only)
    clearAll() {
        Object.values(this.keys).forEach(key => {
            this.remove(key);
        });
        this.init();
    }
};

// Initialize storage on load
Storage.init();
