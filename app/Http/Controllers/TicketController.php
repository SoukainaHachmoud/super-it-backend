<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\TicketMessage;
use App\Models\TicketHistory;

class TicketController extends Controller
{

    public function index()
    {
        $tickets = Ticket::with('employe')->orderBy('created_at', 'desc')->get();

        return response()->json($tickets, 200);
    }

    public function getAgentTickets($employe_id)
    {
        $tickets = Ticket::where('employe_id', $employe_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tickets);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'employe_id'  => 'required',
                'sujet'       => 'required|string|max:255',
                'description' => 'required|string',
                'categorie'   => 'required|string',
                'urgence'     => 'required|string',
            ]);

            $ticket = Ticket::create([
                'employe_id'  => $request->employe_id,
                'sujet'       => $request->sujet,
                'description' => $request->description,
                'categorie'   => $request->categorie,
                'urgence'     => $request->urgence,
                'statut'      => 'Nouveau',
            ]);

            $nomEmploye = $ticket->employe->Nom_Prenom ?? 'un collaborateur';

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'action'    => "Ticket créé par " . $nomEmploye
            ]);

            return response()->json($ticket, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket introuvable.'], 404);
        }
        return response()->json($ticket);
    }


    public function updateStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:Nouveau,En cours,Résolu',
        ]);

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket introuvable.'], 404);
        }

        $ticket->statut = $request->statut;
        $ticket->save();

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'action'    => "Statut changé à [ " . $request->statut . " ] par l'administrateur"
        ]);

        return response()->json($ticket);
    }


    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket introuvable.'], 404);
        }

        if (in_array($ticket->statut, ['Résolu', 'Fermé'])) {
            return response()->json(['message' => 'Impossible de modifier un ticket résolu ou fermé.'], 403);
        }

        $request->validate([
            'sujet'       => 'required|string|max:255',
            'description' => 'required|string',
            'categorie'   => 'required|string',
            'urgence'     => 'required|string',
        ]);

        $ticket->update([
            'sujet'       => $request->sujet,
            'description' => $request->description,
            'categorie'   => $request->categorie,
            'urgence'     => $request->urgence,
        ]);

        return response()->json($ticket);
    }


    public function destroy($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket introuvable.'], 404);
        }

        $ticket->delete();

        return response()->json(['message' => 'Ticket supprimé avec succès.']);
    }



        public function getMessages($id)
        {
            $ticket = Ticket::findOrFail($id);

            $formattedMessages = $ticket->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
                return [
                    'id'     => $msg->id,
                    'sender' => $msg->sender,
                    'text'   => $msg->text ?? $msg->message,
                    'time'   => $msg->created_at ? $msg->created_at->format('H:i') : '--:--'
                ];
            });

            return response()->json($formattedMessages);
        }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'sender'  => 'nullable|string'
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $id,
            'sender'    => $request->input('sender', 'admin'),
            'text'      => $request->message
        ]);

        return response()->json(['success' => true, 'message' => $message], 201);
    }


    public function getHistory($id)
    {
        $ticket = Ticket::findOrFail($id);

        $formattedHistory = $ticket->histories->map(function ($log) {
            return [
                'action' => $log->action,
                'time'   => $log->created_at->format('H:i')
            ];
        });

        return response()->json($formattedHistory);
    }


        public function getAgentNotifications()
        {
            $agentId = auth()->id();

            $tickets = Ticket::where('employe_id', $agentId)->get();
            $notifications = [];

            foreach ($tickets as $ticket) {
                $hasUnreadAdminMessage = $ticket->messages()
                    ->where('sender', 'admin')
                    ->where('is_read', 0)
                    ->latest()
                    ->first();

                if ($hasUnreadAdminMessage) {
                    $notifications[] = [
                        'id'      => $ticket->id,
                        'message' => "L'administrateur a répondu à votre ticket : " . ($ticket->sujet ?? 'Sans sujet'),
                        'time'    => $hasUnreadAdminMessage->created_at->diffForHumans()
                    ];
                }
            }

            return response()->json($notifications);
        }

    public function markAsRead($id)
    {
        TicketMessage::where('ticket_id', $id)
            ->where('sender', 'admin')
            ->update(['is_read' => 1]);

        return response()->json(['success' => true, 'message' => 'Messages marqués comme lus.']);
    }


    public function getAdminNotifications()
    {
        $tickets = Ticket::with('employe')
            ->whereHas('messages', function($query) {
                $query->where('sender', 'agent')
                      ->where('is_read', 0);
            })
            ->get();

        $notifications = [];

        foreach ($tickets as $ticket) {
            $lastMessage = $ticket->messages()
                ->where('sender', 'agent')
                ->where('is_read', 0)
                ->latest()
                ->first();

            if ($lastMessage) {
                $nomAgent = $ticket->employe->Nom_Prenom ?? 'Un agent';
                $notifications[] = [
                    'id'      => $ticket->id,
                    'message' => "{$nomAgent} a répondu au ticket : " . ($ticket->sujet ?? 'Sans sujet'),
                    'time'    => $lastMessage->created_at->diffForHumans()
                ];
            }
        }

        return response()->json($notifications);
    }

    public function markAsReadAdmin($id)
    {
        TicketMessage::where('ticket_id', $id)
            ->where('sender', 'agent')
            ->update(['is_read' => 1]);

        return response()->json(['success' => true, 'message' => 'Messages agents marqués comme lus par l\'admin.']);
    }



}
