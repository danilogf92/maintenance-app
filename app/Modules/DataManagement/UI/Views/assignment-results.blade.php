@if($run->link_results)
 <h4>Vínculos de equipos</h4>
 <div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Relación</th><th>Vinculados en esta carga</th><th>Ya vinculados</th><th>Pendientes</th><th>Conflictos</th></tr></thead><tbody>
  @foreach(['client_id'=>'Cliente','institution_id'=>'Centro médico'] as $column=>$label)
   @php($links=collect($run->link_results)->where('column',$column))
   <tr><td>{{ $label }}</td>@foreach(['linked','existing','pending','conflict'] as $status)<td>{{ $links->where('status',$status)->count() }}</td>@endforeach</tr>
  @endforeach
 </tbody></table></div>
 <details><summary>Revisar todos los equipos y sus asignaciones</summary><div class="dm-table-wrap"><table class="dm-table"><thead><tr><th>Equipo · ID de origen</th><th>Relación</th><th>Resultado</th><th>Historiales · IDs de origen</th><th>Detalle</th></tr></thead><tbody>
  @foreach($run->link_results as $link)<tr><td>{{ $link['source_id'] }}</td><td>{{ $link['column']==='client_id' ? 'Cliente' : 'Centro médico' }}</td>
   <td>{{ ['linked'=>'Vinculado','existing'=>'Ya vinculado','pending'=>'Pendiente','conflict'=>'Conflicto'][$link['status']] }}</td><td>{{ implode(', ',$link['history_ids']) }}</td>
   <td>{{ $link['reason']==='verified' ? 'Referencias verificadas' : \App\Modules\DataManagement\Configuration\HistoryIssues::message($link['reason']) }}</td></tr>@endforeach
 </tbody></table></div></details>
@endif
