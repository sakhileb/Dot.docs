<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h1 style="font-family:'Syne',sans-serif;font-size:1.35rem;font-weight:800;color:#f4f4f5;margin:0 0 0.2rem;">Document Dashboard</h1>
                <p style="margin:0;font-size:0.78rem;color:#71717a;">Your writing workspace — create, collaborate, and publish.</p>
            </div>
            <a href="{{ route('documents.index') }}" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.6rem 1.25rem;border-radius:9999px;background:linear-gradient(135deg,#0284c7,#0369a1);font-family:'Syne',sans-serif;font-size:0.8rem;font-weight:700;color:#fff;text-decoration:none;box-shadow:0 6px 18px rgba(2,132,199,0.3);">
                <span class="material-symbols-rounded" style="font-size:17px;">add_circle</span>
                New Document
            </a>
        </div>
    </x-slot>

    <div style="padding:2rem 2.5rem 3rem;">

        {{-- KPI Row --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2.5rem;">

            {{-- My Documents --}}
            <div style="background:#141416;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-10px;right:-10px;width:70px;height:70px;border-radius:9999px;background:rgba(2,132,199,0.08);"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <span style="font-size:0.7rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.12em;font-family:'Syne',sans-serif;">My Documents</span>
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(2,132,199,0.15);display:flex;align-items:center;justify-content:center;">
                        <span class="material-symbols-rounded" style="font-size:18px;color:#0284c7;">description</span>
                    </div>
                </div>
                <div style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#f4f4f5;line-height:1;">{{ $myDocs }}</div>
                <div style="margin-top:0.5rem;font-size:0.72rem;color:#5a6585;">Total authored</div>
            </div>

            {{-- Shared with Me --}}
            <div style="background:#141416;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-10px;right:-10px;width:70px;height:70px;border-radius:9999px;background:rgba(99,102,241,0.08);"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <span style="font-size:0.7rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.12em;font-family:'Syne',sans-serif;">Shared with Me</span>
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(99,102,241,0.15);display:flex;align-items:center;justify-content:center;">
                        <span class="material-symbols-rounded" style="font-size:18px;color:#818cf8;">group</span>
                    </div>
                </div>
                <div style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#f4f4f5;line-height:1;">{{ $sharedDocs }}</div>
                <div style="margin-top:0.5rem;font-size:0.72rem;color:#5a6585;">Collaborating on</div>
            </div>

            {{-- Public Docs --}}
            <div style="background:#141416;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-10px;right:-10px;width:70px;height:70px;border-radius:9999px;background:rgba(34,197,94,0.08);"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <span style="font-size:0.7rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.12em;font-family:'Syne',sans-serif;">Public Docs</span>
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center;">
                        <span class="material-symbols-rounded" style="font-size:18px;color:#22c55e;">public</span>
                    </div>
                </div>
                <div style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#f4f4f5;line-height:1;">{{ $publicDocs }}</div>
                <div style="margin-top:0.5rem;font-size:0.72rem;color:#5a6585;">Publicly accessible</div>
            </div>

            {{-- AI Suggestions Pending --}}
            <div style="background:#141416;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:1.4rem 1.5rem;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-10px;right:-10px;width:70px;height:70px;border-radius:9999px;background:rgba(245,193,16,0.08);"></div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <span style="font-size:0.7rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.12em;font-family:'Syne',sans-serif;">AI Suggestions</span>
                    <div style="width:34px;height:34px;border-radius:9px;background:rgba(245,193,16,0.15);display:flex;align-items:center;justify-content:center;">
                        <span class="material-symbols-rounded" style="font-size:18px;color:#f5c110;">auto_awesome</span>
                    </div>
                </div>
                <div style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#f4f4f5;line-height:1;">{{ $aiSuggestions }}</div>
                <div style="margin-top:0.5rem;font-size:0.72rem;color:#5a6585;">Awaiting review</div>
            </div>
        </div>

        {{-- Recent Documents --}}
        <section style="margin-bottom:2.5rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-family:'Syne',sans-serif;font-size:0.8rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.14em;margin:0;">Recent Documents</h2>
                <a href="{{ route('documents.index') }}" style="font-size:0.78rem;color:#0284c7;text-decoration:none;font-weight:600;">View all &rarr;</a>
            </div>

            @if($recentDocs->isEmpty())
                <div style="background:#141416;border:1px solid rgba(255,255,255,0.06);border-radius:14px;padding:3.5rem 2rem;text-align:center;">
                    <span class="material-symbols-rounded" style="font-size:48px;color:#2a3450;display:block;margin-bottom:1rem;">description</span>
                    <p style="color:#5a6585;font-size:0.875rem;margin:0 0 1.25rem;">No documents yet. Start writing your first doc.</p>
                    <a href="{{ route('documents.index') }}" style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.55rem 1.25rem;border-radius:9999px;background:linear-gradient(135deg,#0284c7,#0369a1);font-family:'Syne',sans-serif;font-size:0.78rem;font-weight:700;color:#fff;text-decoration:none;">
                        <span class="material-symbols-rounded" style="font-size:16px;">add_circle</span>
                        Create Document
                    </a>
                </div>
            @else
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
                    @foreach($recentDocs as $doc)
                        <div style="background:#141416;border:1px solid rgba(255,255,255,0.06);border-radius:14px;padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(2,132,199,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                            <div>
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:0.85rem;">
                                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(2,132,199,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <span class="material-symbols-rounded" style="font-size:18px;color:#0284c7;">description</span>
                                    </div>
                                    @if($doc->collaborators->count() > 0)
                                        <span style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.65rem;color:#71717a;background:rgba(255,255,255,0.05);border-radius:9999px;padding:0.2rem 0.55rem;border:1px solid rgba(255,255,255,0.06);">
                                            <span class="material-symbols-rounded" style="font-size:12px;">group</span>
                                            {{ $doc->collaborators->count() }}
                                        </span>
                                    @endif
                                    @if($doc->is_public)
                                        <span style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.65rem;color:#22c55e;background:rgba(34,197,94,0.1);border-radius:9999px;padding:0.2rem 0.55rem;border:1px solid rgba(34,197,94,0.2);">
                                            <span class="material-symbols-rounded" style="font-size:12px;">public</span>
                                            Public
                                        </span>
                                    @endif
                                </div>
                                <h4 style="font-family:'Syne',sans-serif;font-size:0.875rem;font-weight:700;color:#f4f4f5;margin:0 0 0.5rem;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    {{ $doc->title ?: 'Untitled Document' }}
                                </h4>
                                <div style="display:flex;align-items:center;gap:0.35rem;margin-bottom:0.75rem;">
                                    <span style="font-size:0.65rem;font-weight:600;color:#5a6585;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:4px;padding:0.15rem 0.45rem;font-family:'Syne',sans-serif;text-transform:uppercase;letter-spacing:0.08em;">v{{ $doc->version }}</span>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.5rem;padding-top:0.85rem;border-top:1px solid rgba(67,70,86,0.15);">
                                <span style="font-size:0.7rem;color:#5a6585;">{{ $doc->updated_at->diffForHumans() }}</span>
                                <a href="{{ route('documents.edit', $doc->uuid) }}" style="display:inline-flex;align-items:center;gap:0.3rem;font-size:0.72rem;font-weight:700;color:#0284c7;text-decoration:none;font-family:'Syne',sans-serif;" onmouseover="this.style.color='#7dd3fc'" onmouseout="this.style.color='#0284c7'">
                                    Open
                                    <span class="material-symbols-rounded" style="font-size:14px;">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Shared with Me --}}
        @if($recentShared->isNotEmpty())
        <section>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-family:'Syne',sans-serif;font-size:0.8rem;font-weight:700;color:#71717a;text-transform:uppercase;letter-spacing:0.14em;margin:0;">Shared with Me</h2>
            </div>
            <div style="background:#141416;border:1px solid rgba(255,255,255,0.06);border-radius:14px;overflow:hidden;">
                @foreach($recentShared as $collab)
                    @if($collab->document)
                    <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;{{ !$loop->last ? 'border-bottom:1px solid rgba(67,70,86,0.15);' : '' }}" onmouseover="this.style.background='rgba(2,132,199,0.04)'" onmouseout="this.style.background='transparent'">
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-symbols-rounded" style="font-size:18px;color:#818cf8;">description</span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-family:'Syne',sans-serif;font-size:0.875rem;font-weight:700;color:#f4f4f5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $collab->document->title ?: 'Untitled Document' }}
                            </div>
                            <div style="font-size:0.72rem;color:#5a6585;margin-top:0.15rem;">
                                by {{ $collab->document->owner?->name ?? 'Unknown' }}
                            </div>
                        </div>
                        @php
                            $roleColors = [
                                'admin'  => ['bg' => 'rgba(245,193,16,0.12)',  'color' => '#f5c110',  'border' => 'rgba(245,193,16,0.25)'],
                                'editor' => ['bg' => 'rgba(34,197,94,0.12)',   'color' => '#22c55e',  'border' => 'rgba(34,197,94,0.25)'],
                                'viewer' => ['bg' => 'rgba(129,140,248,0.12)', 'color' => '#818cf8',  'border' => 'rgba(129,140,248,0.25)'],
                            ];
                            $rc = $roleColors[$collab->role] ?? $roleColors['viewer'];
                        @endphp
                        <span style="font-size:0.65rem;font-weight:700;color:{{ $rc['color'] }};background:{{ $rc['bg'] }};border:1px solid {{ $rc['border'] }};border-radius:9999px;padding:0.2rem 0.65rem;text-transform:uppercase;letter-spacing:0.1em;font-family:'Syne',sans-serif;flex-shrink:0;">
                            {{ $collab->role }}
                        </span>
                        <a href="{{ route('documents.edit', $collab->document->uuid) }}" style="display:inline-flex;align-items:center;gap:0.25rem;font-size:0.72rem;font-weight:700;color:#0284c7;text-decoration:none;font-family:'Syne',sans-serif;flex-shrink:0;" onmouseover="this.style.color='#7dd3fc'" onmouseout="this.style.color='#0284c7'">
                            Open <span class="material-symbols-rounded" style="font-size:14px;">arrow_forward</span>
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </section>
        @endif

    </div>
</x-app-layout>
