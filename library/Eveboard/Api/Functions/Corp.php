<?php
namespace Eveboard\Api\Functions;

/**
 * @method array  killLog(int $beforeKillID) The Kill Log API displays a list of the 100 most recent kills made by members of the specified corporation. The kill log also contains information about any items dropped in a kill and detailed information about the victim of each kill. The user must either have the role of Director or CEO of the corporation.
 * @method array  outpostList()              The Outpost List API allows you to pull information about the corporationʼs outposts and requires a full API key from the director (or CEO) or the corporation that the outpost belongs to.
 * @method object corporationSheet()         The Corporation Sheet API returns a complete readout of a corporation's details as shown in the image below. Full access is not required to retrieve information from this API, but a CEO / Director of the corporation can view more information than others. Ordinary members of a corporation (i.e. not CEO or Director) and non-members will see the same limited information.
 */
class Corp extends SectionAbstract {
	
}