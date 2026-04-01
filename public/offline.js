if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => console.log('Service Worker registered'))
            .catch(error => console.error('Service Worker registration failed:', error));
    });
}

// Offline support
let isOnline = navigator.onLine;

window.addEventListener('online', () => {
    isOnline = true;
    console.log('Application is online');
    window.dispatchEvent(new CustomEvent('app:online'));
});

window.addEventListener('offline', () => {
    isOnline = false;
    console.log('Application is offline');
    window.dispatchEvent(new CustomEvent('app:offline'));
});

// Offline document caching
class OfflineDocumentManager {
    constructor() {
        this.storageKey = 'offline_documents';
        this.maxDocuments = 20;
    }

    async saveDocument(documentId, content, title) {
        if (!isOnline) {
            const docs = await this.getDocuments();
            docs[documentId] = {
                id: documentId,
                content: content,
                title: title,
                savedAt: new Date().toISOString(),
                synced: false,
            };
            await this.saveToStorage(docs);
            return true;
        }
        return false;
    }

    async getDocuments() {
        const stored = localStorage.getItem(this.storageKey);
        return stored ? JSON.parse(stored) : {};
    }

    async getDocument(documentId) {
        const docs = await this.getDocuments();
        return docs[documentId] || null;
    }

    async deleteDocument(documentId) {
        const docs = await this.getDocuments();
        delete docs[documentId];
        await this.saveToStorage(docs);
    }

    async saveToStorage(docs) {
        const docArray = Object.values(docs);
        if (docArray.length > this.maxDocuments) {
            // Remove oldest
            docArray.sort((a, b) => new Date(a.savedAt) - new Date(b.savedAt));
            docArray.splice(0, docArray.length - this.maxDocuments);
        }
        const remaining = {};
        docArray.forEach(doc => remaining[doc.id] = doc);
        localStorage.setItem(this.storageKey, JSON.stringify(remaining));
    }

    async syncPendingDocuments() {
        if (!isOnline) return false;

        const docs = await this.getDocuments();
        let syncedCount = 0;

        for (const [id, doc] of Object.entries(docs)) {
            if (!doc.synced) {
                try {
                    const response = await fetch(`/api/documents/${id}/sync`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            content: doc.content,
                            title: doc.title,
                        }),
                    });

                    if (response.ok) {
                        doc.synced = true;
                        syncedCount++;
                    }
                } catch (error) {
                    console.error(`Failed to sync document ${id}:`, error);
                }
            }
        }

        await this.saveToStorage(docs);
        return syncedCount > 0;
    }
}

window.offlineDocumentManager = new OfflineDocumentManager();
