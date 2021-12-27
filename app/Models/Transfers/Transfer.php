<?php

namespace App\Models\Transfers;

use App\Models\ApiLeagues;
use App\Models\ApiTeamLeagues;
use App\Models\ApiTeams;
use App\Models\ApiTeamsPlayers;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $guarded = [];

    protected $appends = ['league_ids'];

    public function season() {
        return $this->belongsTo(TransferSeason::class, 'transfer_season_id');
    }

    public function player() {
        return $this->belongsTo(ApiTeamsPlayers::class, 'player_id', 'player_id')
            ->select(['team_id', 'player_id', 'common_name', 'fullname', 'image_path', 'slug', 'nationality']);
    }

    public function fromTeam() {
        return $this->belongsTo(ApiTeams::class, 'from_team_id', 'team_id')
            ->select(['team_id', 'name', 'name_geo', 'logo_path', 'slug']);
    }

    public function toTeam() {
        return $this->belongsTo(ApiTeams::class, 'to_team_id', 'team_id')
            ->select(['team_id', 'name', 'name_geo', 'logo_path', 'slug']);
    }

    public function leagues() {
        return $this->belongsToMany(ApiLeagues::class, 'transfer_leagues', 'transfer_id', 'league_id');
    }

    public function getLeagueIdsAttribute() {
        if($this->relationLoaded('leagues')) return $this->leagues->pluck('league_id');

        return collect();
    }
}
