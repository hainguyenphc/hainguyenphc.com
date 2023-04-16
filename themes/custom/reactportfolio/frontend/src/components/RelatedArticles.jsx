import { Link } from "react-router-dom";

export default function RelatedArticles({ related }) {
  return related.length === 0 ? null : <><h2 id="related-articles-label">Related Articles</h2>
    <div id="related-articles">
      {related.map((each) => <div key={each.id} className={`related-article related-article--${each.id}`}><Link to={each.url}>
        <div className="related-article--image"><img src={each.teaser_image_url} alt={each.title} /></div>
        <div className="related-article--title">{each.title}</div>
      </Link></div>)}
    </div>
  </>;
}
